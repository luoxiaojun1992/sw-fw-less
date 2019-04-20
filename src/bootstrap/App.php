<?php

namespace SwFwLess\bootstrap;

class App
{
    use \SwFwLess\middlewares\traits\Parser;

    const VERSION = '0.1.0';

    const SAPI = 'swoole';

    const EVENT_RESPONSING = 'app.responsing';
    const EVENT_RESPONSED = 'app.responsed';

    /** @var \Swoole\Http\Server */
    private $swHttpServer;

    /** @var \FastRoute\Dispatcher */
    private $httpRouteDispatcher;

    /**
     * App constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        $this->bootstrap();

        $this->swHttpServer = new \Swoole\Http\Server(
            config('server.host'),
            config('server.port')
        );

        $serverConfig = [
            'reactor_num' => config('server.reactor_num'),
            'worker_num' => config('server.worker_num'),
            'daemonize' => config('server.daemonize'),
            'backlog' => config('server.backlog'),
            'max_request' => config('server.max_request'),
            'dispatch_mode' => config('server.dispatch_mode'),
        ];
        if (!empty($pidFile = config('server.pid_file'))) {
            $serverConfig['pid_file'] = $pidFile;
        }
        $this->swHttpServer->set($serverConfig);

        $this->swHttpServer->on('start', [$this, 'swHttpStart']);
        $this->swHttpServer->on('workerStart', [$this, 'swHttpWorkerStart']);
        $this->swHttpServer->on('request', [$this, 'swHttpRequest']);
        $this->swHttpServer->on('shutdown', [$this, 'swHttpShutdown']);
    }

    /**
     * @throws \Exception
     */
    private function checkEnvironment()
    {
        if (!extension_loaded('swoole')) {
            throw new \Exception('Swoole extension is not installed.');
        }

        if (version_compare(PHP_VERSION, '7.1') < 0) {
            throw new \Exception('PHP7.1+ is not installed.');
        }
    }

    private function loadRouter()
    {
        $this->httpRouteDispatcher = \FastRoute\simpleDispatcher(function (\FastRoute\RouteCollector $r) {
            $routerConfig = config('router');
            foreach ($routerConfig['single'] as $router) {
                array_unshift($router[2], $router[1]);
                $r->addRoute($router[0], $router[1], $router[2]);
            }
            foreach ($routerConfig['group'] as $prefix => $routers) {
                $r->addGroup($prefix, function (\FastRoute\RouteCollector $r) use ($routers, $prefix) {
                    foreach ($routers as $router) {
                        array_unshift($router[2], '/' . trim($prefix, '/') . '/' . trim($router[1], '/'));
                        $r->addRoute($router[0], $router[1], $router[2]);
                    }
                });
            }
            if (config('monitor.switch')) {
                $r->addGroup('/internal', function (\FastRoute\RouteCollector $r) {
                    $r->addRoute(
                        'GET',
                        '/monitor/pool',
                        ['/internal/monitor/pool', \SwFwLess\services\internals\MonitorService::class, 'pool']
                    );
                    $r->addRoute(
                        'GET',
                        '/log/flush',
                        ['/internal/log/flush', \SwFwLess\services\internals\LogService::class, 'flush']
                    );
                    $r->addRoute(
                        'POST',
                        '/chaos/fault/{id}',
                        ['/internal/chaos/fault/{id}', \SwFwLess\services\internals\ChaosService::class, 'injectFault']
                    );
                    $r->addRoute(
                        'GET',
                        '/chaos/fault/{id}',
                        ['/internal/chaos/fault/{id}', \SwFwLess\services\internals\ChaosService::class, 'fetchFault']
                    );
                });
            }
        });
    }

    /**
     * @throws \Exception
     */
    private function bootstrap()
    {
        $this->checkEnvironment();

        require_once __DIR__ . '/../components/functions.php';

        \Swoole\Runtime::enableCoroutine();

        //Load Env
        if (file_exists(APP_BASE_PATH . '.env')) {
            (new \Dotenv\Dotenv(APP_BASE_PATH))->load();
        }

        //Init Config
        \SwFwLess\components\Config::init(require_once APP_BASE_PATH . 'config/app.php');

        //Boot providers
        \SwFwLess\components\provider\KernelProvider::bootApp();
    }

    private function getRequestHandler($request)
    {
        $appRequest = \SwFwLess\components\http\Request::fromSwRequest($request);

        //Middleware
        $middlewareNames = config('middleware.middleware');
        array_push($middlewareNames, \SwFwLess\middlewares\Route::class);
        /** @var \SwFwLess\middlewares\MiddlewareContract[]|\SwFwLess\middlewares\AbstractMiddleware[] $middlewareConcretes */
        $middlewareConcretes = [];
        foreach ($middlewareNames as $i => $middlewareName) {
            list($middlewareClass, $middlewareOptions) = $this->parseMiddlewareName($middlewareName);

            /** @var \SwFwLess\middlewares\AbstractMiddleware $middlewareConcrete */
            $middlewareConcrete = \SwFwLess\facades\Container::get($middlewareClass);
            $middlewareConcrete->setParameters([$appRequest]);
            if ($middlewareConcrete instanceof \SwFwLess\middlewares\Route) {
                $middlewareConcrete->setOptions($this->httpRouteDispatcher);
            } else {
                $middlewareConcrete->setOptions($middlewareOptions);
            }
            if (isset($middlewareConcretes[$i - 1])) {
                $middlewareConcretes[$i - 1]->setNext($middlewareConcrete);
            }

            array_push($middlewareConcretes, $middlewareConcrete);
        }

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
     * @throws \Exception
     */
    public function swHttpWorkerStart($server, $id)
    {
        //Overload Env
        if (file_exists(APP_BASE_PATH . '.env')) {
            (new \Dotenv\Dotenv(APP_BASE_PATH))->overload();
        }

        //Init Config
        \SwFwLess\components\Config::init(require APP_BASE_PATH . 'config/app.php');

        $this->loadRouter();

        //Inject Swoole Server
        \SwFwLess\facades\Container::set('swoole.server', $server);

        //Boot providers
        \SwFwLess\components\provider\KernelProvider::bootRequest();
    }

    public function swHttpRequest(\Swoole\Http\Request $request, \Swoole\Http\Response $response)
    {
        try {
            clearstatcache();

            $this->swResponse($this->swfRequest(function () use ($request) {
                return $this->getRequestHandler($request)->call();
            }), $response);
        } catch (\Throwable $e) {
            $this->swResponse($this->swfRequest(function () use ($e) {
                return \SwFwLess\components\ErrorHandler::handle($e);
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

    private function swResponse(\SwFwLess\components\http\Response $swfResponse, \Swoole\Http\Response $swResponse)
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

        \SwFwLess\components\provider\KernelProvider::shutdown();
    }

    private function swResponseWithEvents($callback, \SwFwLess\components\http\Response $swfResponse)
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
            \SwFwLess\components\filewatcher\Watcher::create(
                config('hot_reload.driver'),
                config('hot_reload.watch_dirs'),
                config('hot_reload.excluded_dirs'),
                config('hot_reload.watch_suffixes')
            )->watch(\SwFwLess\components\filewatcher\Watcher::EVENT_MODIFY, function ($event) use ($server) {
                $server->reload();
            });
        });
    }

    public function run()
    {
        $this->swHttpServer->start();
    }
}
