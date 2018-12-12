<?php

require_once __DIR__ . '/vendor/autoload.php';

\Swoole\Runtime::enableCoroutine();

//Dot Env
(new Dotenv\Dotenv(__DIR__))->load();

//Init Config
\App\components\Config::init(require_once __DIR__ . '/config/app.php');

//Log
\App\components\Log::create(
    \App\components\Config::get('log.path'),
    \App\components\Config::get('log.level'),
    \App\components\Config::get('log.pool_size'),
    \App\components\Config::get('log.buffer_max_size')
);

//Route Config
$dispatcher = FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $r) {
    $routerConfig = \App\components\Config::get('router');
    foreach ($routerConfig as $router) {
        $r->addRoute($router[0], $router[1], $router[2]);
    }
});
