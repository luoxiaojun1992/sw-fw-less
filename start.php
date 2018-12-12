<?php

require_once __DIR__ . '/bootstrap.php';

$http = new \Swoole\Http\Server(\App\components\Config::get('server.host'), \App\components\Config::get('server.port'));

$http->on('start', function ($server){
    echo 'Server started.', PHP_EOL;
    echo 'Listening ' . $server->ports[0]->host . ':' . $server->ports[0]->port, PHP_EOL;
});

$http->set(array(
    'reactor_num' => \App\components\Config::get('server.reactor_num'),
    'worker_num' => \App\components\Config::get('server.worker_num'),
    'daemonize' => \App\components\Config::get('server.daemonize'),
    'backlog' => \App\components\Config::get('server.backlog'),
    'max_request' => \App\components\Config::get('server.max_request'),
));

$http->on('workerStart', function($server, $id) {
    \App\components\RedisPool::create(
        \App\components\Config::get('redis.host'),
        \App\components\Config::get('redis.port'),
        \App\components\Config::get('redis.timeout'),
        \App\components\Config::get('redis.pool_size'),
        \App\components\Config::get('redis.passwd'),
        \App\components\Config::get('redis.db')
    );
    \App\components\MysqlPool::create(
        \App\components\Config::get('mysql.dsn'),
        \App\components\Config::get('mysql.username'),
        \App\components\Config::get('mysql.passwd'),
        \App\components\Config::get('mysql.options'),
        \App\components\Config::get('mysql.pool_size')
    );
});

$http->on("request", function (\Swoole\Http\Request $request, \Swoole\Http\Response $response) use ($dispatcher) {
    try {
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
                if ($handler[0] instanceof \App\services\BaseService) {
                    $handler[0]->setRequest((new \App\components\Request())->setSwRequest($request));
                }
                $vars = $routeInfo[2];

                ob_start();
                /**
                 * @var \App\components\Response $res
                 */
                $res = call_user_func_array($handler, $vars);
                $obContent = ob_get_contents();
                $content = $res->getContent();
                if (!$content && ob_get_length() > 0) {
                    $content = $obContent;
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
        $response->status(!is_string($e->getCode()) && $e->getCode() ? $e->getCode() : 500);
        $response->end(nl2br($e->getMessage() . PHP_EOL . $e->getTraceAsString()));
    }
});

$http->start();
