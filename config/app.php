<?php

return [
    //Router
    'router' => [
        ['GET', '/redis', [\App\services\DemoService::class, 'redis']],
        ['GET', '/mysql', [\App\services\DemoService::class, 'mysql']],
        ['GET', '/http', [\App\services\DemoService::class, 'http']],
    ],

    //Server
    'server' => [
        'host' => \App\components\Helper::env('SERVER_HOST', '127.0.0.1'),
        'port' => \App\components\Helper::env('SERVER_PORT', 9501),
        'reactor_num' => \App\components\Helper::env('SERVER_REACTOR_NUM', 8),
        'worker_num' => \App\components\Helper::env('SERVER_WORKER_NUM', 32),
        'daemonize' => \App\components\Helper::env('SERVER_DAEMONIZE', false),
        'backlog' => \App\components\Helper::env('SERVER_BACKLOG', 128),
        'max_request' => \App\components\Helper::env('SERVER_MAX_REQUEST', 0),
    ],

    //Redis
    'redis' => [
        'host' => '127.0.0.1',
        'port' => 6379,
        'timeout' => 1,
        'pool_size' => 5,
        'passwd' => null,
        'db' => 0,
    ],

    //MySQL
    'mysql' => [
        'dsn' => 'mysql:dbname=sw_test;host=127.0.0.1',
        'username' => 'root',
        'passwd' => null,
        'options' => [
            \PDO::ATTR_CASE => \PDO::CASE_NATURAL,
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_ORACLE_NULLS => \PDO::NULL_NATURAL,
            \PDO::ATTR_STRINGIFY_FETCHES => false,
            \PDO::ATTR_EMULATE_PREPARES => false,
        ],
        'pool_size' => 5,
    ],
];
