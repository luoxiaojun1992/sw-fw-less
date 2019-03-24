<?php

class App
{
    const VERSION = '0.1.0';

    const EVENT_RESPONSING = 'app.responsing';
    const EVENT_RESPONSED = 'app.responsed';

    /** @var \Swoole\Http\Server */
    private $swHttpServer;

    /** @var \FastRoute\Dispatcher */
    private $httpRouteDispatcher;

    /**
     * App constructor.
     * @throws ReflectionException
     */
    public function __construct()
    {
        $this->bootstrap();

        $this->swHttpServer = new \Swoole\Http\Server(
            config('server.host'),
            config('server.port')
        );

        $this->swHttpServer->set([
            'reactor_num' => config('server.reactor_num'),
            'worker_num' => config('server.worker_num'),
            'daemonize' => config('server.daemonize'),
            'backlog' => config('server.backlog'),
            'max_request' => config('server.max_request'),
            'dispatch_mode' => config('server.dispatch_mode'),
        ]);

        $this->swHttpServer->on('start', [$this, 'swHttpStart']);
        $this->swHttpServer->on('workerStart', [$this, 'swHttpWorkerStart']);
        $this->swHttpServer->on('request', [$this, 'swHttpRequest']);
        $this->swHttpServer->on('shutdown', [$this, 'swHttpShutdown']);
    }

    /**
     * @throws ReflectionException
     */
    private function bootstrap()
    {
        require_once __DIR__ . '/../app/components/functions.php';

        \Swoole\Runtime::enableCoroutine();

        //Dot Env
        if (file_exists(__DIR__ . '/../.env')) {
            (new Dotenv\Dotenv(__DIR__ . '/../'))->load();
        }

        //Init Config
        \App\components\Config::init(require_once __DIR__ . '/../config/app.php');

        //Boot providers
        \App\components\core\KernelProvider::bootApp();

        //Route Config
        $this->httpRouteDispatcher = FastRoute\simpleDispatcher(function (FastRoute\RouteCollector $r) {
            $routerConfig = config('router');
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
            if (config('monitor.switch')) {
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
        $middlewareNames = config('middleware');
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

    public function swHttpStart(\Swoole\Http\Server $server)
    {
        echo 'Server started.', PHP_EOL;
        echo 'Listening ' . $server->ports[0]->host . ':' . $server->ports[0]->port, PHP_EOL;

        $this->hotReload($server);
    }

    public function swHttpShutdown(\Swoole\Http\Server $server)
    {
        echo 'Server shutdown.', PHP_EOL;
    }

    /**
     * @param $server
     * @param $id
     * @throws Exception
     */
    public function swHttpWorkerStart($server, $id)
    {
        //Boot providers
        \App\components\core\KernelProvider::bootRequest();
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

        $this->swResponseWithEvents(function () use ($swResponse, $swfResponse) {
            $swResponse->end($swfResponse->getContent());
        }, $swfResponse);

        \App\components\core\KernelProvider::shutdown();
    }

    private function swResponseWithEvents($callback, \App\components\http\Response $swfResponse)
    {
        event(new \Cake\Event\Event(
            static::EVENT_RESPONSING,
            null,
            [
                'response' => $swfResponse,
            ]
        ));

        $responsingAt = microtime(true) * 1000;

        call_user_func($callback);

        event(new \Cake\Event\Event(
            static::EVENT_RESPONSED,
            null,
            [
                'response' => $swfResponse,
                'time' => microtime(true) * 1000 - $responsingAt,
            ]
        ));
    }

    private function hotReload(\Swoole\Http\Server $server)
    {
        go(function () use ($server) {
            $watcher = Kwf\FileWatcher\Watcher::create('.');
            $watcher->addListener(Kwf\FileWatcher\Event\Modify::NAME, function ($e) use ($server) {
                $server->reload();
            });
            $watcher->start();
        });
    }

    public function run()
    {
        $this->swHttpServer->start();
    }
}
