<?php

require_once __DIR__ . '/vendor/autoload.php';

//Route Config
$dispatcher = FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $r) {
    $r->addRoute('GET', '/foo', [App\services\DemoService::class, 'foo']);
});

$http = new \Swoole\Http\Server("127.0.0.1", 9501);

$http->set(array(
    'reactor_num' => 8,
    'worker_num' => 32,
    'daemonize' => false,
    'backlog' => 128,
    'max_request' => 0,
));

$http->on('workerStart', function($server, $id) {
    \App\components\Redis::create('127.0.0.1', 6379, 1, 5);
});

$http->on("request", function (\Swoole\Http\Request $request, \Swoole\Http\Response $response) use ($dispatcher) {
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
            $vars = $routeInfo[2];
            // ... call $handler with $vars
            $response->end(call_user_func_array($handler, $vars));
            break;
        default:
            $response->end();
    }
});

$http->start();
