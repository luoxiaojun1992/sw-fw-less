<?php

require_once __DIR__ . '/bootstrap.php';

$http = new \Swoole\Http\Server(\App\components\Config::get('server.host'), \App\components\Config::get('server.port'));

$http->on('start', function ($server){
    echo 'Server started.', PHP_EOL;
    echo 'Listening ' . $server->ports[0]->host . ':' . $server->ports[0]->port, PHP_EOL;
});

$http->set([
    'reactor_num' => \App\components\Config::get('server.reactor_num'),
    'worker_num' => \App\components\Config::get('server.worker_num'),
    'daemonize' => \App\components\Config::get('server.daemonize'),
    'backlog' => \App\components\Config::get('server.backlog'),
    'max_request' => \App\components\Config::get('server.max_request'),
    'dispatch_mode' => \App\components\Config::get('server.dispatch_mode'),
]);

$http->on('workerStart', function($server, $id) {
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
});

$http->on("request", function (\Swoole\Http\Request $request, \Swoole\Http\Response $response) use ($dispatcher) {
    try {
        clearstatcache();

        $requestUri = $request->server['request_uri'];
        if (false !== $pos = strpos($requestUri, '?')) {
            $requestUri = substr($requestUri, 0, $pos);
        }
        $requestUri = rawurldecode($requestUri);

        $routeInfo = $dispatcher->dispatch($request->server['request_method'], $requestUri);
        switch ($routeInfo[0]) {
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
                $handler = $routeInfo[1];
                $handler[0] = new $handler[0];
                $appRequest = (new \App\components\Request())->setSwRequest($request);
                if ($handler[0] instanceof \App\services\BaseService) {
                    $handler[0]->setRequest($appRequest);
                }

                $callBack = [$handler[0], $handler[1]];
                $vars = $routeInfo[2];

                //Middleware
                if (isset($handler[2])) {
                    $handler[2] = array_merge(\App\components\Config::get('middleware'), $handler[2]);
                    $middlewareCount = count($handler[2]);
                    foreach ($handler[2] as $i => $middlewareClass) {
                        if ($middlewareCount > 1) {
                            if ($i > 0) {
                                $previousMiddleware = new $handler[2][$i - 1];
                                $currentMiddleware = new $middlewareClass;
                                $previousMiddleware->setNext($currentMiddleware);
                                if ($i == ($middlewareCount - 1)) {
                                    $currentMiddleware->terminal([$handler[0], $handler[1]], $routeInfo[2]);
                                }
                                if ($i == 1) {
                                    $callBack = [$previousMiddleware, 'handle'];
                                    $vars = [$appRequest];
                                }
                            }
                        } else {
                            $currentMiddleware = new $middlewareClass;
                            $currentMiddleware->terminal([$handler[0], $handler[1]], $routeInfo[2]);
                            $callBack = [$currentMiddleware, 'handle'];
                            $vars = [$appRequest];
                        }
                    }
                }

                ob_start();
                /**
                 * @var \App\components\Response $res
                 */
                $res = call_user_func_array($callBack, $vars);
                $content = $res->getContent();
                if (!$content && ob_get_length() > 0) {
                    $content = ob_get_contents();
                }
                ob_end_clean();

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
        }
        ob_end_clean();

        $response->status($res->getStatus());
        if ($headers = $res->getHeaders()) {
            foreach ($headers as $key => $value) {
                $response->header($key, $value);
            }
        }
        $response->end($content);
    }
});

$http->start();
