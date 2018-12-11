<?php

require_once __DIR__ . '/vendor/autoload.php';

\Swoole\Runtime::enableCoroutine();

//Route Config
$dispatcher = FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $r) {
    $r->addRoute('GET', '/redis', [App\services\DemoService::class, 'redis']);
    $r->addRoute('GET', '/mysql', [App\services\DemoService::class, 'mysql']);
    $r->addRoute('GET', '/http', [App\services\DemoService::class, 'http']);
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
    \App\components\Mysql::create('mysql:dbname=sw_test;host=127.0.0.1', 'root', '', [], 5);
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
});

$http->start();
