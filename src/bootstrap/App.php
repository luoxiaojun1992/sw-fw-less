<?php

namespace SwFwLess\bootstrap;

use Cron\CronExpression;
use SwFwLess\components\config\apollo\ClientBuilder;
use SwFwLess\components\grpc\Status;
use SwFwLess\components\provider\KernelProvider;
use SwFwLess\facades\Container;
use SwFwLess\facades\Log;
use SwFwLess\facades\RateLimit;
use Swoole\Http\Server;
use Swoole\Server\Task;

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
            'open_http2_protocol' => config('server.open_http2_protocol'),
            'task_worker_num' => config('server.task_worker_num'),
            'task_enable_coroutine' => config('server.task_enable_coroutine'),
        ];
        if (!empty($pidFile = config('server.pid_file'))) {
            $serverConfig['pid_file'] = $pidFile;
        }
        $this->swHttpServer->set($serverConfig);

        $this->swHttpServer->on('start', [$this, 'swHttpStart']);
        $this->swHttpServer->on('workerStart', [$this, 'swHttpWorkerStart']);
        $this->swHttpServer->on('request', [$this, 'swHttpRequest']);
        $this->swHttpServer->on('shutdown', [$this, 'swHttpShutdown']);
        $this->swHttpServer->on('task', [$this, 'swTask']);
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
                    $r->addGroup('/monitor', function (\FastRoute\RouteCollector $r) {
                        $r->addRoute(
                            'GET',
                            '/pool',
                            ['/internal/monitor/pool', \SwFwLess\services\internals\MonitorService::class, 'pool']
                        );
                        $r->addRoute(
                            'GET',
                            '/swoole',
                            ['/internal/monitor/swoole', \SwFwLess\services\internals\MonitorService::class, 'swoole']
                        );
                    });
                    $r->addRoute(
                        'GET',
                        '/log/flush',
                        ['/internal/log/flush', \SwFwLess\services\internals\LogService::class, 'flush']
                    );
                    $r->addGroup('/chaos', function (\FastRoute\RouteCollector $r) {
                        $r->addGroup('/fault', function (\FastRoute\RouteCollector $r) {
                            $r->addRoute(
                                'POST',
                                '/{id}',
                                ['/internal/chaos/fault/{id}', \SwFwLess\services\internals\ChaosService::class, 'injectFault']
                            );
                            $r->addRoute(
                                'GET',
                                '/{id}',
                                ['/internal/chaos/fault/{id}', \SwFwLess\services\internals\ChaosService::class, 'fetchFault']
                            );
                        });
                    });
                });
            }
        });
    }

    /**
     * @param bool $reboot
     * @throws \Exception
     */
    private function bootstrap($reboot = false)
    {
        $this->checkEnvironment();

        require_once __DIR__ . '/../components/functions.php';

        //Load Env
        if (file_exists(APP_BASE_PATH . '.env')) {
            $dotEnv = (new \Dotenv\Dotenv(APP_BASE_PATH));
            if ($reboot) {
                $dotEnv->overload();
            } else {
                $dotEnv->load();
            }
        }

        //Init Config
        \SwFwLess\components\Config::init(
            APP_BASE_PATH . 'config/app',
            defined('CONFIG_FORMAT') ? CONFIG_FORMAT : 'array'
        );

        $this->loadRouter();

        //Boot providers
        KernelProvider::init(config('providers'));
        KernelProvider::bootApp();
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
            $middlewareConcrete = config('route_di_switch') ?
                Container::make($middlewareClass) :
                new $middlewareClass;
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

        $server->task([
            'type' => 'boot',
        ]);
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
    public function swHttpWorkerStart(Server $server, $id)
    {
        //Overload Env
        if (file_exists(APP_BASE_PATH . '.env')) {
            (new \Dotenv\Dotenv(APP_BASE_PATH))->overload();
        }

        //Init Config
        \SwFwLess\components\Config::init(
            APP_BASE_PATH . 'config/app',
            defined('CONFIG_FORMAT') ? CONFIG_FORMAT : 'array'
        );

        //Inject Swoole Server
        \SwFwLess\components\swoole\Server::setInstance($server);

        //Boot providers
        KernelProvider::init(config('providers'));
        KernelProvider::bootWorker();
    }

    public function swHttpRequest(\Swoole\Http\Request $request, \Swoole\Http\Response $response)
    {
        try {
            KernelProvider::bootRequest();

            clearstatcache();

            $this->swResponse($this->swfRequest(function () use ($request) {
                return $this->getRequestHandler($request)->call();
            }), $response, $request);
        } catch (\Throwable $e) {
            $this->swResponse($this->swfRequest(function () use ($e) {
                return \SwFwLess\components\ErrorHandler::handle($e);
            }), $response, $request);
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

    private function swResponse(
        \SwFwLess\components\http\Response $swfResponse,
        \Swoole\Http\Response $swResponse,
        \Swoole\Http\Request $swRequest
    )
    {
        $httpCode = $swfResponse->getStatus();

        if (isset($swRequest->header['content-type'])) {
            if (substr($swRequest->header['content-type'], 0, 16) === 'application/grpc') {
                $grpcStatus = Status::status($httpCode);
                $grpcMessage = urlencode(urlencode(Status::msg($grpcStatus)));
                $swfResponse->trailer('grpc-status', $grpcStatus);
                $swfResponse->trailer('grpc-message', $grpcMessage);
            }
        }

        $swResponse->status($httpCode);

        foreach ($swfResponse->getHeaders() as $key => $value) {
            $swResponse->header($key, $value);
        }

        if (isset($swRequest->header['te'])) {
            if (substr($swRequest->header['te'], 0, 8) === 'trailers') {
                if ($trailers = $swfResponse->getTrailers()) {
                    $trailerHeader = implode(', ', array_keys($trailers));
                    $swfResponse->header('trailer', $trailerHeader);
                    $swResponse->header('trailer', $trailerHeader);
                    foreach ($trailers as $key => $value) {
                        $swResponse->trailer($key, $value);
                    }
                }
            }
        }

        $this->swResponseWithEvents(function () use ($swResponse, $swfResponse) {
            $swResponse->end($swfResponse->getContent());
        }, $swfResponse);

        KernelProvider::shutdown();
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

    public function swTask(Server $server, Task $task)
    {
        $data = $task->data;
        if ($data['type'] === 'job') {
            $job = $data['data']['job'];
            if (is_callable($job)) {
                call_user_func($job);
            } elseif (is_string($job)) {
                shell_exec($job);
            }
        } elseif ($data['type'] === 'boot') {
            $this->hotReload();
            $this->registerScheduler();
            $this->pullApolloConfig();
        }
    }

    private function hotReload()
    {
        if (!config('hot_reload.switch')) {
            return;
        }

        go(function () {
            \SwFwLess\components\filewatcher\Watcher::create(
                config('hot_reload.driver'),
                config('hot_reload.watch_dirs'),
                config('hot_reload.excluded_dirs'),
                config('hot_reload.watch_suffixes')
            )->watch(\SwFwLess\components\filewatcher\Watcher::EVENT_MODIFY, function ($event) {
                $this->bootstrap(true);
                $this->swHttpServer->reload();
            });
        });
    }

    private function registerScheduler()
    {
        swoole_timer_tick(60000, function () {
            $schedules = config('scheduler');
            foreach ($schedules as $i => $schedule) {
                if (CronExpression::factory($schedule['schedule'])->isDue()) {
                    $replica = $schedule['replica'] ?? 0;
                    $jobName = $schedule['name'] ?? ('job_' . ((string)$i));
                    if (!is_array($schedule['jobs'])) {
                        $schedule['jobs'] = [$schedule['jobs']];
                    }
                    if ($replica > 0) {
                        if (!RateLimit::pass(
                            'rate_limit:kernel:scheduler:' . $jobName,
                            0,
                            $replica
                        )) {
                            continue;
                        }
                    }

                    foreach ($schedule['jobs'] as $job) {
                        go(function () use ($job, $jobName) {
                            try {
                                if (is_callable($job)) {
                                    call_user_func($job);
                                } elseif (is_string($job)) {
                                    shell_exec($job);
                                }
                            } catch (\Throwable $e) {
                                Log::error(
                                    'Internal scheduler error:' . $e->getMessage() . '|' . $e->getTraceAsString()
                                );
                            } finally {
                                RateLimit::clear('rate_limit:kernel:scheduler:' . $jobName);
                            }
                        });
                    }
                }
            }
        });
    }

    private function pullApolloConfig()
    {
        if (!config('apollo.enable', false)) {
            return;
        }

        $notificationId = -1;
        $apolloConfig = config('apollo');

        swoole_timer_tick(60000, function () use (&$notificationId, $apolloConfig) {
            if (ClientBuilder::create()
                ->setNamespace($apolloConfig['namespace'])
                ->setCluster($apolloConfig['cluster'])
                ->setAppId($apolloConfig['app_id'])
                ->setConfigServer($apolloConfig['config_server'])
                ->setNotificationInterval($apolloConfig['notification_interval'])
                ->build()
                ->notification($notificationId)
            ) {
                $this->bootstrap(true);
                $this->swHttpServer->reload();
            }
        });
    }

    public function run()
    {
        $this->swHttpServer->start();
    }
}
