<?php

return [
    'single' => [
        ['GET', '/ping', [\App\services\DemoService::class, 'ping']],
        ['GET', '/redis', [\App\services\DemoService::class, 'redis', [\App\middlewares\Cors::class]]],
        ['GET', '/mysql', [\App\services\DemoService::class, 'mysql']],
        ['GET', '/http', [\App\services\DemoService::class, 'http']],
        ['GET', '/es', [\App\services\DemoService::class, 'es']],
        ['GET', '/file', [\App\services\DemoService::class, 'file']],
        ['GET', '/qiniu', [\App\services\DemoService::class, 'qiniu']],
        ['GET', '/rabbitmq', [\App\services\DemoService::class, 'rabbitmq']],
        ['GET', '/alioss', [\App\services\DemoService::class, 'alioss']],
        ['GET', '/hbase', [\App\services\DemoService::class, 'hbase']],
        ['GET', '/cache', [\App\services\DemoService::class, 'cache']],
        ['GET', '/jwt', [\App\services\DemoService::class, 'jwt']],
    ],
    'group' => [
        '/dining' => [
            ['GET', '/menu', [\App\services\DiningService::class, 'menu']],
            ['GET', '/ordered', [\App\services\DiningService::class, 'ordered', [\App\components\auth\Middleware::class]]],
            ['POST', '/order', [\App\services\DiningService::class, 'order', [\App\components\auth\Middleware::class]]],
            ['POST', '/login', [\App\services\DiningService::class, 'login']],
        ],
    ],
];
