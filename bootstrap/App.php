<?php

require_once __DIR__ . '/../vendor/autoload.php';

class App
{
    /** @var \Swoole\Http\Server */
    private $swHttpServer;

    /** @var \FastRoute\Dispatcher */
    private $httpRouteDispatcher;

    public function __construct()
    {
        $this->bootstrap();

        $this->swHttpServer = new \Swoole\Http\Server(
            \App\components\Config::get('server.host'),
            \App\components\Config::get('server.port')
        );

        $this->swHttpServer->set([
            'reactor_num' => \App\components\Config::get('server.reactor_num'),
            'worker_num' => \App\components\Config::get('server.worker_num'),
            'daemonize' => \App\components\Config::get('server.daemonize'),
            'backlog' => \App\components\Config::get('server.backlog'),
            'max_request' => \App\components\Config::get('server.max_request'),
            'dispatch_mode' => \App\components\Config::get('server.dispatch_mode'),
        ]);

        $this->swHttpServer->on('start', [$this, 'swHttpStart']);
        $this->swHttpServer->on('workerStart', [$this, 'swHttpWorkerStart']);
        $this->swHttpServer->on('request', [$this, 'swHttpRequest']);
    }

    private function bootstrap()
    {
        \Swoole\Runtime::enableCoroutine();

        //Counter
        \App\components\utils\swoole\Counter::init();

        //Dot Env
        if (file_exists(__DIR__ . '/../.env')) {
            (new Dotenv\Dotenv(__DIR__ . '/../'))->load();
        }

        //Init Config
        \App\components\Config::init(require_once __DIR__ . '/../config/app.php');

        //Timezone
        date_default_timezone_set(\App\components\Config::get('timezone'));

        //Events
        foreach (\App\components\Config::get('events') as $eventName => $eventListeners) {
            foreach ($eventListeners as $eventListener) {
                \App\facades\Event::on($eventName, $eventListener);
            }
        }

        //Route Config
        $this->httpRouteDispatcher = FastRoute\simpleDispatcher(function (FastRoute\RouteCollector $r) {
            $routerConfig = \App\components\Config::get('router');
            foreach ($routerConfig['single'] as $router) {
                array_unshift($router[2], $router[1]);
                $r->addRoute($router[0], $router[1], $router[2]);
            }
            foreach ($routerConfig['group'] as $prefix => $routers) {
                $r->addGroup($prefix, function (FastRoute\RouteCollector $r) use ($routers, $prefix) {
                    foreach ($routers as $router) {
                        array_unshift($router[2], '/' . trim($prefix, '/') . '/' . trim($router[1], '/'));
                        $r->addRoute($router[0], $router[1], $router[2]);
                    }
                });
            }
            if (\App\components\Config::get('monitor.switch')) {
                $r->addRoute('GET', '/monitor/pool', ['/monitor/pool', \App\services\internals\MonitorService::class, 'pool']);
                $r->addRoute('GET', '/log/flush', ['/log/flush', \App\services\internals\LogService::class, 'flush']);
            }
        });
    }

    /**
     * @param $middlewareName
     * @return array
     */
    private function parseMiddlewareName($middlewareName)
    {
        if (strpos($middlewareName, ':') > 0) {
            return explode(':', $middlewareName);
        }

        return [$middlewareName, null];
    }

    private function getRequestHandler($request, $routeInfo)
    {
        $appRequest = \App\components\http\Request::fromSwRequest($request);

        $controllerAction = $routeInfo[1];
        $route = $controllerAction[0];
        $appRequest->setRoute($route);
        $controllerName = $controllerAction[1];
        $action = $controllerAction[2];
        $parameters = $routeInfo[2];
        $controller = new $controllerName;
        if ($controller instanceof \App\services\BaseService) {
            $controller->setRequest($appRequest);
        }
        $controller->setHandler($action)->setParameters($parameters);

        //Middleware
        $middlewareNames = \App\components\Config::get('middleware');
        if (isset($controllerAction[3])) {
            $middlewareNames = array_merge($middlewareNames, $controllerAction[3]);
        }
        /** @var \App\middlewares\MiddlewareContract[]|\App\middlewares\AbstractMiddleware[] $middlewareConcretes */
        $middlewareConcretes = [];
        foreach ($middlewareNames as $i => $middlewareName) {
            list($middlewareClass, $middlewareOptions) = $this->parseMiddlewareName($middlewareName);

            /** @var \App\middlewares\AbstractMiddleware $middlewareConcrete */
            $middlewareConcrete = new $middlewareClass;
            $middlewareConcrete->setParameters([$appRequest])->setOptions($middlewareOptions);
            if (isset($middlewareConcretes[$i - 1])) {
                $middlewareConcretes[$i - 1]->setNext($middlewareConcrete);
            }

            array_push($middlewareConcretes, $middlewareConcrete);
        }
        $middlewareConcretesCount = count($middlewareConcretes);
        if ($middlewareConcretesCount > 0) {
            $middlewareConcretes[$middlewareConcretesCount - 1]->setNext($controller);
        }
        array_push($middlewareConcretes, $controller);

        return $middlewareConcretes[0];
    }

    public function swHttpStart($server)
    {
        echo 'Server started.', PHP_EOL;
        echo 'Listening ' . $server->ports[0]->host . ':' . $server->ports[0]->port, PHP_EOL;
    }

    public function swHttpWorkerStart($server, $id)
    {
        //Log
        if (\App\components\Config::get('log.switch')) {
            \App\components\Log::create(
                \App\components\Config::get('log.path'),
                \App\components\Config::get('log.level'),
                \App\components\Config::get('log.pool_size'),
                \App\components\Config::get('log.buffer_max_size'),
                \App\components\Config::get('log.name'),
                \App\components\Config::get('log.reserve_days')
            );
        }

        //Redis
        if (\App\components\Config::get('redis.switch')) {
            \App\components\RedisPool::create(
                \App\components\Config::get('redis.host'),
                \App\components\Config::get('redis.port'),
                \App\components\Config::get('redis.timeout'),
                \App\components\Config::get('redis.pool_size'),
                \App\components\Config::get('redis.passwd'),
                \App\components\Config::get('redis.db'),
                \App\components\Config::get('redis.prefix')
            );

            //Rate limiter
            \App\components\RateLimit::create(
                \App\components\RedisPool::create()
            );
        }

        //MySQL
        if (\App\components\Config::get('mysql.switch')) {
            \App\components\MysqlPool::create(
                \App\components\Config::get('mysql.dsn'),
                \App\components\Config::get('mysql.username'),
                \App\components\Config::get('mysql.passwd'),
                \App\components\Config::get('mysql.options'),
                \App\components\Config::get('mysql.pool_size')
            );
        }

        //Elasticsearch
        if (\App\components\Config::get('elasticsearch.switch')) {
            \App\components\es\Manager::create();
        }

        //Storage
        if (\App\components\Config::get('storage.switch')) {
            \App\components\storage\Storage::init();
        }

        //AMQP
        if (\App\components\Config::get('amqp.switch')) {
            \App\components\amqp\ConnectionPool::create();
        }

        //Trace
        if (\App\components\Config::get('trace.switch')) {
            \App\components\Trace::create(\App\components\Config::get('trace.zipkin_url'));
        }

        //Hbase
        if (\App\components\Config::get('hbase.switch')) {
            \App\components\hbase\HbasePool::create();
        }
    }

    public function swHttpRequest(\Swoole\Http\Request $request, \Swoole\Http\Response $response)
    {
        try {
            clearstatcache();

            $requestUri = $request->server['request_uri'];
            if (false !== $pos = strpos($requestUri, '?')) {
                $requestUri = substr($requestUri, 0, $pos);
            }
            $requestUri = rawurldecode($requestUri);

            $routeInfo = $this->httpRouteDispatcher->dispatch($request->server['request_method'], $requestUri);
            $routeResult = $routeInfo[0];
            switch ($routeResult) {
                case FastRoute\Dispatcher::NOT_FOUND:
                    // ... 404 Not Found
                    $this->swResponse(
                        \App\components\http\Response::output(null, 404),
                        $response
                    );
                    break;
                case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
                    $allowedMethods = $routeInfo[1];
                    // ... 405 Method Not Allowed
                    $this->swResponse(
                        \App\components\http\Response::output(null, 405),
                        $response
                    );
                    break;
                case FastRoute\Dispatcher::FOUND:
                    $this->swResponse($this->swfRequest(function () use ($request, $routeInfo) {
                        return $this->getRequestHandler($request, $routeInfo)->call();
                    }), $response);
                    break;
                default:
                    $this->swResponse(
                        \App\components\http\Response::output(null),
                        $response
                    );
            }
        } catch (\Exception $e) {
            $this->swResponse($this->swfRequest(function () use ($e) {
                return \App\components\ErrorHandler::handle($e);
            }), $response);
        }
    }

    private function swfRequest(callable $callback)
    {
        ob_start();

        $swfResponse = call_user_func($callback);

        $content = $swfResponse->getContent();
        if (!$content && ob_get_length() > 0) {
            $swfResponse->setContent(ob_get_contents());
            ob_end_clean();
        } else {
            ob_end_flush();
        }

        return $swfResponse;
    }

    private function swResponse(\App\components\http\Response $swfResponse, \Swoole\Http\Response $swResponse)
    {
        $swResponse->status($swfResponse->getStatus());
        if ($headers = $swfResponse->getHeaders()) {
            foreach ($headers as $key => $value) {
                $swResponse->header($key, $value);
            }
        }

        //todo before after event aop hook

        $swResponse->end($swfResponse->getContent());
    }

    public function run()
    {
        $this->swHttpServer->start();
    }
}
