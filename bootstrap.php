<?php

require_once __DIR__ . '/vendor/autoload.php';

\Swoole\Runtime::enableCoroutine();

//Dot Env
if (file_exists(__DIR__  . '/.env')) {
    (new Dotenv\Dotenv(__DIR__))->load();
}

//Init Config
\App\components\Config::init(require_once __DIR__ . '/config/app.php');

//Timezone
date_default_timezone_set(\App\components\Config::get('timezone'));

//Route Config
$dispatcher = FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $r) {
    $routerConfig = \App\components\Config::get('router');
    foreach ($routerConfig['single'] as $router) {
        $r->addRoute($router[0], $router[1], $router[2]);
    }
    foreach ($routerConfig['group'] as $prefix => $routers) {
        $r->addGroup($prefix, function (FastRoute\RouteCollector $r) use ($routers) {
            foreach ($routers as $router) {
                $r->addRoute($router[0], $router[1], $router[2]);
            }
        });
    }
    if (\App\components\Config::get('monitor.switch')) {
        $r->addRoute('GET', '/monitor/pool', [\App\services\internals\MonitorService::class, 'pool']);
    }
});
