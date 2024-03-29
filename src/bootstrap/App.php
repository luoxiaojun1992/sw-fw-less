<?php

namespace SwFwLess\bootstrap;

use Cron\CronExpression;
use FastRoute\Dispatcher;
use SwFwLess\components\Config;
use SwFwLess\components\config\apollo\ClientBuilder;
use SwFwLess\components\event\Event;
use SwFwLess\components\functions;
use SwFwLess\components\grpc\Status;
use SwFwLess\components\http\Response;
use SwFwLess\components\pool\ObjectPool;
use SwFwLess\components\provider\KernelProvider;
use SwFwLess\components\router\Router;
use SwFwLess\facades\Container;
use SwFwLess\facades\Log;
use SwFwLess\facades\RateLimit;
use SwFwLess\middlewares\ClosureMiddleware;
use SwFwLess\middlewares\Parser;
use Swoole\Http\Server;
use Swoole\Server\Task;
use Symfony\Component\Console\Output\ConsoleOutput;

class App extends Kernel
{
    use \SwFwLess\middlewares\traits\Parser;

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
        parent::__construct();

        $this->swHttpServer = new \Swoole\Http\Server(
            functions\config('server.host', '0.0.0.0'),
            functions\config('server.port', 9501)
        );

        $serverConfig = [
            'reactor_num' => functions\config('server.reactor_num', swoole_cpu_num() * 2),
            'worker_num' => functions\config('server.worker_num', swoole_cpu_num() * 2),
            'daemonize' => functions\config('server.daemonize'),
            'backlog' => functions\config('server.backlog', 128),
            'max_request' => functions\config('server.max_request'),
            'dispatch_mode' => functions\config('server.dispatch_mode', 2),
            'open_http2_protocol' => functions\config('server.open_http2_protocol'),
            'task_worker_num' => functions\config('server.task_worker_num'),
            'task_enable_coroutine' => functions\config('server.task_enable_coroutine'),
            'open_tcp_nodelay' => functions\config('server.open_tcp_nodelay', true),
            'tcp_fastopen' => functions\config('server.tcp_fastopen', true),
            'max_coroutine' => functions\config('server.max_coroutine', 3000),
            'open_cpu_affinity' => functions\config('server.open_cpu_affinity', true),
            'socket_buffer_size' => functions\config('server.socket_buffer_size', 2 * 1024 * 1024),
            'enable_static_handler' => functions\config('server.enable_static_handler', false),
            'document_root' => functions\config('server.document_root', APP_BASE_PATH . 'static'),
            'http_autoindex' => functions\config('server.http_autoindex', false),
            'http_index_files' => functions\config('server.http_index_files', []),
            'static_handler_locations' => [],
        ];
        if (!empty($pidFile = functions\config('server.pid_file'))) {
            $serverConfig['pid_file'] = $pidFile;
        }
        if (functions\config('monitor.switch')) {
            $serverConfig['enable_static_handler'] = true;
            $serverConfig['http_autoindex'] = true;
            if (!in_array('index.html', $serverConfig['http_index_files'])) {
                $serverConfig['http_index_files'][] = 'index.html';
            }
            $serverConfig['static_handler_locations'][] = functions\config(
                'monitor.url', '/tools/monitor'
            );
        }
        $this->swHttpServer->set($serverConfig);

        $this->swHttpServer->on('start', [$this, 'swHttpStart']);
        $this->swHttpServer->on('workerStart', [$this, 'swHttpWorkerStart']);
        $this->swHttpServer->on('workerStop', [$this, 'swHttpWorkerStop']);
        $this->swHttpServer->on('request', [$this, 'swHttpRequest']);
        $this->swHttpServer->on('shutdown', [$this, 'swHttpShutdown']);
        $this->swHttpServer->on('task', [$this, 'swTask']);
    }

    private function loadRouter()
    {
        /**
         * Store the http router in the app instance.
         * Use the app instance as a container instead of a di container.
         */
        $this->httpRouteDispatcher = Router::createDispatcher();
    }

    /**
     * @param bool $reboot
     * @throws \Exception
     */
    protected function bootstrap($reboot = false)
    {
        $this->setSapi(static::SAPI);

        parent::bootstrap($reboot);

        KernelProvider::bootApp();
    }

    private function getDirectRequestHandler($request)
    {
        $router = Router::create(
            \SwFwLess\components\http\Request::fromSwRequest($request),
            $this->httpRouteDispatcher
        );
        try {
            $routeInfo = $router->parseRouteInfo();
            switch ($routeInfo[0]) {
                case Dispatcher::NOT_FOUND:
                    // ... 404 Not Found
                    $response = Response::output('', 404);
                    break;
                case Dispatcher::METHOD_NOT_ALLOWED:
                    // ... 405 Method Not Allowed
                    $response = Response::output('', 405);
                    break;
                case Dispatcher::FOUND:
                    return $router->createController();
                default:
                    $response = Response::output('');
            }
            return new Class($response){
                protected $response;

                public function __construct($response)
                {
                    $this->response = $response;
                }

                public function call() {
                    return $this->response;
                }
            };
        } catch (\Throwable $e) {
            throw $e;
        } finally {
            ObjectPool::create()->release($router);
        }
    }

    private function getRequestHandler($request)
    {
        $appRequest = \SwFwLess\components\http\Request::fromSwRequest($request);

        //inline optimization, see SwFwLess\components\di\Container::routeDiSwitch()
        $routeDiSwitch = (\SwFwLess\components\Config::get(
                'di_switch', \SwFwLess\components\di\Container::DEFAULT_DI_SWITCH)) &&
            (\SwFwLess\components\Config::get('route_di_switch'));

        //Middleware
        $middlewareNames = Config::get('middleware.middleware');

        $middlewareNames[] = \SwFwLess\components\router\Middleware::class;
        /** @var \SwFwLess\middlewares\MiddlewareContract[]|\SwFwLess\middlewares\AbstractMiddleware[] $middlewareConcretes */
        $prevMiddlewareConcrete = null;
        $firstMiddlewareConcrete = null;
        foreach ($middlewareNames as $middlewareName) {
            $isClosureMiddleware = is_callable($middlewareName);
            $isRouteMiddleware = (
                (!$isClosureMiddleware) && ($middlewareName === \SwFwLess\components\router\Middleware::class)
            );

            list($middlewareClass, $middlewareOptions) = $isRouteMiddleware ?
                [$middlewareName, null] :
                (
                $isClosureMiddleware ?
                    [ClosureMiddleware::class, $middlewareName] :
                    Parser::parseMiddlewareName($middlewareName)
                );

            /** @var \SwFwLess\middlewares\AbstractMiddleware $middlewareConcrete */
            $middlewareConcrete = ObjectPool::create()->pick($middlewareClass) ?: ($routeDiSwitch ?
                Container::make($middlewareClass) :
                new $middlewareClass);

            $firstMiddlewareConcrete = $firstMiddlewareConcrete ?? $middlewareConcrete;

            $middlewareConcrete->setParametersAndOptions(
                [$appRequest],
                $isRouteMiddleware ?
                    $this->httpRouteDispatcher :
                    $middlewareOptions
            );
            ($prevMiddlewareConcrete !== null) && ($prevMiddlewareConcrete->setNext($middlewareConcrete));
            $prevMiddlewareConcrete = $middlewareConcrete;
        }

        return $firstMiddlewareConcrete;
    }

    protected function bootstrapInfo(\Swoole\Http\Server $server)
    {
        $output = new ConsoleOutput();
        $output->writeln('<info>Server started.</info>');
        $output->writeln(
            '<info>Listening ' .
            $server->ports[0]->host . ':' . ((string)($server->ports[0]->port)) .
            '</info>'
        );
        $output->writeln('');
        $output->writeln('<comment>Press Ctrl+C to stop the server.</comment>');
        $output->writeln('');
    }

    public function swHttpStart(\Swoole\Http\Server $server)
    {
        $this->bootstrapInfo($server);
    }

    public function swHttpShutdown(\Swoole\Http\Server $server)
    {
        echo 'Server shutdown.', PHP_EOL;
        KernelProvider::shutdownApp();
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
        $configFormat = defined('CONFIG_FORMAT') ?
            CONFIG_FORMAT :
            Config::DEFAULT_CONFIG_FORMAT;
        \SwFwLess\components\Config::init(
            APP_BASE_PATH . 'config/app',
            $configFormat
        );

        $this->loadRouter();

        //Inject Swoole Server
        if (\SwFwLess\components\di\Container::diSwitch()) {
            \SwFwLess\components\swoole\Server::setInstance($server);
        }

        //Boot providers
        KernelProvider::init(functions\config('providers'));
        KernelProvider::bootWorker();

        if (!$server->taskworker) {
            if ($id === 0) {
                $server->task([
                    'type' => 'boot',
                ]);
            }
        }
    }

    public function swHttpWorkerStop(Server $server, $id)
    {
        KernelProvider::shutdownWorker();
    }

    public function swHttpRequest(\Swoole\Http\Request $request, \Swoole\Http\Response $response)
    {
        try {
            KernelProvider::bootRequest();

            clearstatcache();

            $this->swResponse($this->swfRequest(function () use ($request) {
                return ((Config::get('middleware.switch', false)) ?
                    $this->getRequestHandler($request) :
                    $this->getDirectRequestHandler($request))->call();
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
        if (($content !== null) || (ob_get_length() <= 0)) {
            ob_end_flush();
        } else {
            $swfResponse->setContent(ob_get_contents());
            ob_end_clean();
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

        if (isset($swRequest->header['content-type']) &&
            (substr($swRequest->header['content-type'], 0, 16) === 'application/grpc')
        ) {
            list($grpcStatus, $grpcMessage) = Status::statusAndMsg($httpCode);
            $swfResponse->trailer('grpc-status', $grpcStatus);
            $swfResponse->trailer('grpc-message', urlencode(urlencode($grpcMessage)));
        }

        $swResponse->status($httpCode);

        foreach ($swfResponse->getHeaders() as $key => $value) {
            $swResponse->header($key, $value);
        }

        if (isset($swRequest->header['te']) &&
            (substr($swRequest->header['te'], 0, 8) === 'trailers') &&
            ($trailers = $swfResponse->getTrailers())
        ) {
            $trailerHeader = implode(', ', array_keys($trailers));
            $swfResponse->header('trailer', $trailerHeader);
            $swResponse->header('trailer', $trailerHeader);
            foreach ($trailers as $key => $value) {
                $swResponse->trailer($key, $value);
            }
        }

        $this->swResponseWithEvents(function () use ($swResponse, $swfResponse) {
            $swResponse->end($swfResponse->getContent());
        }, $swfResponse);

        KernelProvider::shutdownResponse();
    }

    private function swResponseWithEvents($callback, \SwFwLess\components\http\Response $swfResponse)
    {
        Event::create()->dispatch(new \Cake\Event\Event(
            static::EVENT_RESPONSING,
            null,
            [
                'response' => $swfResponse,
            ]
        ));

        $responsingAt = microtime(true);

        call_user_func($callback);

        Event::create()->dispatch(new \Cake\Event\Event(
            static::EVENT_RESPONSED,
            null,
            [
                'response' => $swfResponse,
                'time' => (microtime(true) - $responsingAt) * 1000,
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
        if (!functions\config('hot_reload.switch')) {
            return;
        }

        go(function () {
            \SwFwLess\components\filewatcher\Watcher::create(
                functions\config('hot_reload.driver'),
                functions\config('hot_reload.watch_dirs'),
                functions\config('hot_reload.excluded_dirs'),
                functions\config('hot_reload.watch_suffixes')
            )->watch(\SwFwLess\components\filewatcher\Watcher::EVENT_MODIFY, function ($event) {
                if (!functions\config('hot_reload.switch')) {
                    return;
                }

                $this->swHttpServer->reload();
            });
        });
    }

    private function clearSchedulerRateLimit()
    {
        $schedules = functions\config('scheduler');
        foreach ($schedules as $i => $schedule) {
            $replica = $schedule['replica'] ?? 0;
            $jobName = $schedule['name'] ?? ('job_' . ((string)$i));
            if ($replica > 0) {
                RateLimit::clear('rate_limit:kernel:scheduler:' . $jobName);
            }
        }
    }

    private function registerScheduler()
    {
        $this->clearSchedulerRateLimit();

        swoole_timer_tick(60000, function () {
            $schedules = functions\config('scheduler');
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
                        go(function () use ($job, $jobName, $replica) {
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
                                if ($replica > 0) {
                                    RateLimit::clear('rate_limit:kernel:scheduler:' . $jobName);
                                }
                            }
                        });
                    }
                }
            }
        });
    }

    private function pullApolloConfig()
    {
        if (!functions\config('apollo.enable', false)) {
            return;
        }

        $notificationId = -1;

        swoole_timer_tick(60000, function () use (&$notificationId) {
            if (!functions\config('apollo.enable', false)) {
                return;
            }

            $apolloConfig = functions\config('apollo');
            if (ClientBuilder::create()
                ->setNamespace($apolloConfig['namespace'])
                ->setCluster($apolloConfig['cluster'])
                ->setAppId($apolloConfig['app_id'])
                ->setConfigServer($apolloConfig['config_server'])
                ->setNotificationInterval($apolloConfig['notification_interval'])
                ->build()
                ->notification($notificationId)
            ) {
                $this->swHttpServer->reload();
            }
        });
    }

    public function run()
    {
        $this->swHttpServer->start();
    }
}
