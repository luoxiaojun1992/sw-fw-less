<?php

return [
    //Router
    'router' => [
        ['GET', '/redis', [\App\services\DemoService::class, 'redis', [\App\middlewares\Cors::class]]],
        ['GET', '/mysql', [\App\services\DemoService::class, 'mysql']],
        ['GET', '/http', [\App\services\DemoService::class, 'http']],
        ['GET', '/es', [\App\services\DemoService::class, 'es']],
        ['GET', '/file', [\App\services\DemoService::class, 'file']],
        ['GET', '/qiniu', [\App\services\DemoService::class, 'qiniu']],
        ['GET', '/monitor/pool', [\App\services\internals\MonitorService::class, 'pool']],
    ],

    //Server
    'server' => [
        'host' => \App\components\Helper::env('SERVER_HOST', '127.0.0.1'),
        'port' => \App\components\Helper::envInt('SERVER_PORT', 9501),
        'reactor_num' => \App\components\Helper::envInt('SERVER_REACTOR_NUM', 8),
        'worker_num' => \App\components\Helper::envInt('SERVER_WORKER_NUM', 32),
        'daemonize' => \App\components\Helper::envBool('SERVER_DAEMONIZE', false),
        'backlog' => \App\components\Helper::envInt('SERVER_BACKLOG', 128),
        'max_request' => \App\components\Helper::envInt('SERVER_MAX_REQUEST', 0),
        'dispatch_mode' => \App\components\Helper::envInt('SERVER_DISPATCH_MODE', 2),
    ],

    //Redis
    'redis' => [
        'host' => \App\components\Helper::env('REDIS_HOST', '127.0.0.1'),
        'port' => \App\components\Helper::envInt('REDIS_PORT', 6379),
        'timeout' => \App\components\Helper::envDouble('REDIS_TIMEOUT', 1),
        'pool_size' => \App\components\Helper::envInt('REDIS_POOL_SIZE', 5),
        'passwd' => \App\components\Helper::env('REDIS_PASSWD', null),
        'db' => \App\components\Helper::envInt('REDIS_DB', 0),
        'switch' => \App\components\Helper::envInt('REDIS_SWITCH', 0),
        'prefix' => \App\components\Helper::env('REDIS_PREFIX', 'sw-fw-less:'),
    ],

    //MySQL
    'mysql' => [
        'dsn' => \App\components\Helper::env('MYSQL_DSN', 'mysql:dbname=sw_test;host=127.0.0.1'),
        'username' => \App\components\Helper::env('MYSQL_USERNAME', 'root'),
        'passwd' => \App\components\Helper::env('MYSQL_PASSWD', null),
        'options' => [
            \PDO::ATTR_CASE => \PDO::CASE_NATURAL,
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_ORACLE_NULLS => \PDO::NULL_NATURAL,
            \PDO::ATTR_STRINGIFY_FETCHES => false,
            \PDO::ATTR_EMULATE_PREPARES => false,
        ],
        'pool_size' => \App\components\Helper::envInt('MYSQL_POOL_SIZE', 5),
        'switch' => \App\components\Helper::envInt('MYSQL_SWITCH', 0),
    ],

    //Log
    'log' => [
        'path' => \App\components\Helper::env('LOG_PATH', __DIR__ . '/../runtime/logs/app-{date}.log'),
        'level' => \App\components\Helper::envInt('LOG_LEVEL', \Monolog\Logger::DEBUG),
        'pool_size' => \App\components\Helper::envInt('LOG_POOL_SIZE', 100),
        'buffer_max_size' => \App\components\Helper::envInt('LOG_BUFFER_MAX_SIZE', 10),
        'name' => \App\components\Helper::env('LOG_NAME', 'sw-fw-less'),
        'reserve_days' => \App\components\Helper::envInt('LOG_RESERVE_DAYS', 3),
        'switch' => \App\components\Helper::envInt('LOG_SWITCH', 0),
    ],

    //Elasticsearch
    'elasticsearch' => [
        'connections' => [
            'default' => [
                'hosts' => [
                    \App\components\Helper::env('ES_DEFAULT_HOST', '127.0.0.1:9200'),
                ],
                'timeout' => \App\components\Helper::envDouble('ES_TIMEOUT', 1),
            ],
        ],
        'switch' => \App\components\Helper::envInt('ES_SWITCH', 0),
    ],

    //Middleware
    'middleware' => [
        //\App\middlewares\Cors::class,
    ],

    //Cors
    'cors' => [
        'origin' => \App\components\Helper::env('CORS_ORIGIN', ''),
        'switch' => \App\components\Helper::envInt('CORS_SWITCH', 0),
    ],

    //Storage
    'storage' => [
        'base_path' => \App\components\Helper::env('APP_BASE_PATH', __DIR__ . '/../'),
        'switch' => \App\components\Helper::envInt('STORAGE_SWITCH', 0),
        'storage_path' => \App\components\Helper::env('STORAGE_PATH', __DIR__ . '/../runtime/storage/'),
        'types' => ['file', 'qiniu', 'alioss'],
        'ext' => [
            'qiniu' => [
                'default_bucket' => \App\components\Helper::env('QINIU_DEFAULT_BUCKET', 'default'),
                'buckets' => [
                    \App\components\Helper::env('QINIU_DEFAULT_BUCKET', 'default') => [
                        'access_key' => \App\components\Helper::env('QINIU_DEFAULT_ACCESS_KEY', ''),
                        'secret_key' => \App\components\Helper::env('QINIU_DEFAULT_SECRET_KEY', ''),
                        'domain' => \App\components\Helper::env('QINIU_DEFAULT_DOMAIN', ''),
                    ],
                ],
            ],
            'alioss' => [],
        ],
    ],

    //Timezone
    'timezone' => 'PRC',
];
