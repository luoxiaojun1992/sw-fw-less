<?php

return [
    //Router
    'router' => [
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
        ],
        'group' => [
            //
        ],
    ],

    //Server
    'server' => [
        'host' => \App\components\Helper::env('SERVER_HOST', '0.0.0.0'),
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
        'pool_change_event' => \App\components\Helper::envInt('REDIS_POOL_CHANGE_EVENT', 0),
        'report_pool_change' => \App\components\Helper::envInt('REDIS_REPORT_POOL_CHANGE', 0),
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
        'pool_change_event' => \App\components\Helper::envInt('MYSQL_POOL_CHANGE_EVENT', 0),
        'report_pool_change' => \App\components\Helper::envInt('MYSQL_REPORT_POOL_CHANGE', 0),
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
        \App\middlewares\RedLock::class,
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
        'types' => \App\components\Helper::envArray('STORAGE_TYPES', ['file', 'qiniu', 'alioss']),
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
            'alioss' => [
                'default_bucket' => \App\components\Helper::env('ALIOSS_DEFAULT_BUCKET', 'default'),
                'buckets' => [
                    \App\components\Helper::env('ALIOSS_DEFAULT_BUCKET', 'default') => [
                        'access_id' => \App\components\Helper::env('ALIOSS_DEFAULT_ACCESS_ID', ''),
                        'access_secret' => \App\components\Helper::env('ALIOSS_DEFAULT_ACCESS_SECRET', ''),
                        'endpoint' => \App\components\Helper::env('ALIOSS_DEFAULT_ENDPOINT', ''),
                        'timeout' => \App\components\Helper::envDouble('ALIOSS_DEFAULT_TIMEOUT', 1),
                        'connectTimeout' => \App\components\Helper::envDouble('ALIOSS_DEFAULT_CONNECT_TIMEOUT', 1),
                        'isCName' => \App\components\Helper::envBool('ALIOSS_DEFAULT_IS_CNAME', false),
                        'securityToken' => \App\components\Helper::env('ALIOSS_DEFAULT_SECURITY_TOKEN', null),
                        'domain' => \App\components\Helper::env('ALIOSS_DEFAULT_DOMAIN', ''),
                    ],
                ],
            ],
        ],
    ],

    //Timezone
    'timezone' => \App\components\Helper::env('TIMEZONE', 'PRC'),

    //Monitor
    'monitor' => [
        'switch' => \App\components\Helper::envInt('MONITOR_SWITCH', 0),
    ],

    //AMQP
    'amqp' => [
        'pool_size' => \App\components\Helper::envInt('AMQP_POOL_SIZE', 5),
        'switch' => \App\components\Helper::envInt('AMQP_SWITCH', 0),
        'prefix' => \App\components\Helper::env('AMQP_PREFIX', 'sw-fw-less:'),
        'channel_id' => \App\components\Helper::envInt('AMQP_CHANNEL_ID', 1),
        'host' => \App\components\Helper::env('AMQP_HOST', '127.0.0.1'),
        'port' => \App\components\Helper::envInt('AMQP_PORT', 5672),
        'user' => \App\components\Helper::env('AMQP_USER', 'guest'),
        'passwd' => \App\components\Helper::env('AMQP_PASSWD', 'guest'),
        'vhost' => \App\components\Helper::env('AMQP_VHOST', '/'),
        'locale' => \App\components\Helper::env('AMQP_LOCALE', 'en_US'),
        'read_timeout' => \App\components\Helper::envInt('AMQP_READ_TIMEOUT', 3),
        'keepalive' => \App\components\Helper::envBool('AMQP_KEEPALIVE', false),
        'write_timeout' => \App\components\Helper::envInt('AMQP_WRITE_TIMEOUT', 3),
        'heartbeat' => \App\components\Helper::envInt('AMQP_HEARTBEAT', 0),
        'pool_change_event' => \App\components\Helper::envInt('AMQP_POOL_CHANGE_EVENT', 0),
        'report_pool_change' => \App\components\Helper::envInt('AMQP_REPORT_POOL_CHANGE', 0),
    ],

    //Events
    'events' => [
        'redis:pool:change' => [
            function ($event) {
                $count = $event->getData('count');

                if (\App\components\Config::get('redis.report_pool_change')) {
                    if (extension_loaded('swoole')) {
                        \App\components\utils\swoole\Counter::incr('monitor:pool:redis', $count);
                    }
                }
            },
        ],
        'mysql:pool:change' => [
            function ($event) {
                $count = $event->getData('count');

                if (\App\components\Config::get('mysql.report_pool_change')) {
                    if (extension_loaded('swoole')) {
                        \App\components\utils\swoole\Counter::incr('monitor:pool:mysql', $count);
                    }
                }
            },
        ],
        'amqp:pool:change' => [
            function ($event) {
                $count = $event->getData('count');

                if (\App\components\Config::get('amqp.report_pool_change')) {
                    if (extension_loaded('swoole')) {
                        \App\components\utils\swoole\Counter::incr('monitor:pool:amqp', $count);
                    }
                }
            },
        ],
        'hbase:pool:change' => [
            function ($event) {
                $count = $event->getData('count');

                if (\App\components\Config::get('hbase.report_pool_change')) {
                    if (extension_loaded('swoole')) {
                        \App\components\utils\swoole\Counter::incr('monitor:pool:hbase', $count);
                    }
                }
            },
        ],
    ],

    //Trace
    'trace' => [
        'switch' => \App\components\Helper::envInt('TRACE_SWITCH', 0),
        'zipkin_url' => \App\components\Helper::env('TRACE_ZIPKIN_URL', 'http://127.0.0.1:9411/api/v2/spans'),
        'sample_rate' => \App\components\Helper::envDouble('TRACE_SAMPLE_RATE', 0),
        'service_name' => \App\components\Helper::env('TRACE_SERVICE_NAME', 'sw-fw-less'),
    ],

    //Hbase
    'hbase' => [
        'pool_size' => \App\components\Helper::envInt('HBASE_POOL_SIZE', 5),
        'switch' => \App\components\Helper::envInt('HBASE_SWITCH', 0),
        'host' => \App\components\Helper::env('HBASE_HOST', '127.0.0.1'),
        'port' => \App\components\Helper::envInt('HBASE_PORT', 9090),
        'read_timeout' => \App\components\Helper::envInt('HBASE_READ_TIMEOUT', 5000),
        'write_timeout' => \App\components\Helper::envInt('HBASE_WRITE_TIMEOUT', 5000),
        'pool_change_event' => \App\components\Helper::envInt('HBASE_POOL_CHANGE_EVENT', 0),
        'report_pool_change' => \App\components\Helper::envInt('HBASE_REPORT_POOL_CHANGE', 0),
    ],

    //Throttle
    'throttle' => [
        'metric' => function(\App\components\Request $request){
            return $request->uri();
        },
        'period' => \App\components\Helper::envInt('THROTTLE_PERIOD', 60),
        'throttle' => \App\components\Helper::envInt('THROTTLE_THROTTLE', 10000),
    ],
];
