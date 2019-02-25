<?php

require_once __DIR__ . '/bootstrap.php';

$httpServer = new \Swoole\Http\Server(\App\components\Config::get('server.host'), \App\components\Config::get('server.port'));

$httpServer->on('start', function ($server){
    echo 'Server started.', PHP_EOL;
    echo 'Listening ' . $server->ports[0]->host . ':' . $server->ports[0]->port, PHP_EOL;
});

$httpServer->set([
    'reactor_num' => \App\components\Config::get('server.reactor_num'),
    'worker_num' => \App\components\Config::get('server.worker_num'),
    'daemonize' => \App\components\Config::get('server.daemonize'),
    'backlog' => \App\components\Config::get('server.backlog'),
    'max_request' => \App\components\Config::get('server.max_request'),
    'dispatch_mode' => \App\components\Config::get('server.dispatch_mode'),
]);

$httpServer->on('workerStart', function($server, $id) {
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

        //RedLock
        \App\components\RedLock::create(
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
});

$httpServer->on("request", function (\Swoole\Http\Request $request, \Swoole\Http\Response $response) use ($dispatcher) {
    $header = isset($request->header) ? $request->header : [];
    foreach ($header as $k => $v) {
        $header[strtolower($k)] = $v;
    }
    $traceId = isset($header['x-trace-id']) ? $header['x-trace-id'] : null;

    $callback = function () use ($request, $response, $dispatcher, &$traceId) {
        try {
            clearstatcache();

            $requestUri = $request->server['request_uri'];
            if (false !== $pos = strpos($requestUri, '?')) {
                $requestUri = substr($requestUri, 0, $pos);
            }
            $requestUri = rawurldecode($requestUri);

            $routeInfo = $dispatcher->dispatch($request->server['request_method'], $requestUri);
            $routeResult = $routeInfo[0];
            switch ($routeResult) {
                case FastRoute\Dispatcher::NOT_FOUND:
                    // ... 404 Not Found
                    $response->status(404);
                    $response->end();
                    break;
                case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
                    $allowedMethods = $routeInfo[1];
                    // ... 405 Method Not Allowed
                    $response->status(405);
                    $response->end();
                    break;
                case FastRoute\Dispatcher::FOUND:
                    $appRequest = \App\components\Request::fromSwRequest($request)->setTraceId($traceId);

                    $controllerAction = $routeInfo[1];
                    $controllerName = $controllerAction[0];
                    $action = $controllerAction[1];
                    $parameters = $routeInfo[2];
                    $controller = new $controllerName;
                    if ($controller instanceof \App\services\BaseService) {
                        $controller->setRequest($appRequest);
                    }
                    $controller->setHandler($action)->setParameters($parameters);

                    //Middleware
                    $middlewareNames = \App\components\Config::get('middleware');
                    if (isset($controllerAction[2])) {
                        $middlewareNames = array_merge($middlewareNames, $controllerAction[2]);
                    }
                    /** @var \App\middlewares\MiddlewareContract[]|\App\middlewares\AbstractMiddleware[] $middlewareConcretes */
                    $middlewareConcretes = [];
                    foreach ($middlewareNames as $i => $middlewareName) {
                        /** @var \App\middlewares\AbstractMiddleware $middlewareConcrete */
                        $middlewareConcrete = new $middlewareName;
                        $middlewareConcrete->setParameters([$appRequest]);
                        if (isset($middlewareConcretes[$i - 1])) {
                            $middlewareConcretes[$i - 1]->setNext($middlewareConcrete);
                        }
                    }
                    $middlewareConcretesCount = count($middlewareConcretes);
                    if ($middlewareConcretesCount > 0) {
                        $middlewareConcretes[$middlewareConcretesCount - 1]->setNext($controller);
                    }
                    array_push($middlewareConcretes, $controller);

                    ob_start();
                    /**
                     * @var \App\components\Response $res
                     */
                    $res = $middlewareConcretes[0]->call();
                    $content = $res->getContent();
                    if (!$content && ob_get_length() > 0) {
                        $content = ob_get_contents();
                        ob_end_clean();
                    } else {
                        ob_end_flush();
                    }

                    $response->status($res->getStatus());
                    if ($headers = $res->getHeaders()) {
                        foreach ($headers as $key => $value) {
                            $response->header($key, $value);
                        }
                    }

                    $response->end($content);
                    break;
                default:
                    $response->end();
            }
        } catch (\Exception $e) {
            ob_start();
            $res = \App\components\ErrorHandler::handle($e);
            $content = $res->getContent();
            if (!$content && ob_get_length() > 0) {
                $content = ob_get_contents();
                ob_end_clean();
            } else {
                ob_end_flush();
            }

            $response->status($res->getStatus());
            if ($headers = $res->getHeaders()) {
                foreach ($headers as $key => $value) {
                    $response->header($key, $value);
                }
            }
            $response->end($content);
        }
    };

    $traceConfig = \App\components\Config::get('trace');
    $needSample = false;
    if ($traceConfig['switch']) {
        if (!empty($traceConfig['zipkin_url'])) {
            if ($traceId) {
                $needSample = true;
            } elseif (!empty($traceConfig['sample_rate'])) {
                if ($traceConfig['sample_rate'] >= 1) {
                    $needSample = true;
                } else {
                    mt_srand(time());
                    if (mt_rand() / mt_getrandmax() <= $traceConfig['sample_rate']) {
                        $needSample = true;
                    }
                }
            }
        }
    }
    if ($needSample) {
        $serviceName = $traceConfig['service_name'];
        $server = isset($request->server) ? $request->server : [];
        foreach ($server as $k => $v) {
            $server[strtolower($k)] = $v;
        }
        $spanName = isset($server['request_uri']) ? $server['request_uri'] : 'request';
        $traceId = $traceId ? : str_replace('-', '', \Ramsey\Uuid\Uuid::uuid4());
        \App\facades\Trace::span([
            'service_name' => $serviceName,
            'span_name' => $spanName,
            'zipkin_url' => $traceConfig['zipkin_url'],
            'trace_id' => $traceId,
        ], $callback);
    } else {
        call_user_func($callback);
    }
});

$httpServer->start();

//todo app object & run method
