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
        'host' => env('SERVER_HOST', '0.0.0.0'),
        'port' => envInt('SERVER_PORT', 9501),
        'reactor_num' => envInt('SERVER_REACTOR_NUM', 8),
        'worker_num' => envInt('SERVER_WORKER_NUM', 32),
        'daemonize' => envBool('SERVER_DAEMONIZE', false),
        'backlog' => envInt('SERVER_BACKLOG', 128),
        'max_request' => envInt('SERVER_MAX_REQUEST', 0),
        'dispatch_mode' => envInt('SERVER_DISPATCH_MODE', 2),
    ],

    //Redis
    'redis' => [
        'default' => env('REDIS_DEFAULT', 'default'),
        'connections' => [
            env('REDIS_DEFAULT', 'default') => [
                'host' => env('REDIS_HOST', '127.0.0.1'),
                'port' => envInt('REDIS_PORT', 6379),
                'timeout' => envDouble('REDIS_TIMEOUT', 1),
                'pool_size' => envInt('REDIS_POOL_SIZE', 5),
                'passwd' => env('REDIS_PASSWD', null),
                'db' => envInt('REDIS_DB', 0),
                'prefix' => env('REDIS_PREFIX', 'sw-fw-less:'),
            ],
            'zipkin' => [
                'host' => env('REDIS_ZIPKIN_HOST', '127.0.0.1'),
                'port' => envInt('REDIS_ZIPKIN_PORT', 6379),
                'timeout' => envDouble('REDIS_ZIPKIN_TIMEOUT', 1),
                'pool_size' => envInt('REDIS_ZIPKIN_POOL_SIZE', 5),
                'passwd' => env('REDIS_ZIPKIN_PASSWD', null),
                'db' => envInt('REDIS_ZIPKIN_DB', 1),
                'prefix' => env('REDIS_ZIPKIN_PREFIX', 'sw-fw-less:'),
            ],
            'red_lock' => [
                'host' => env('REDIS_RED_LOCK_HOST', '127.0.0.1'),
                'port' => envInt('REDIS_RED_LOCK_PORT', 6379),
                'timeout' => envDouble('REDIS_RED_LOCK_TIMEOUT', 1),
                'pool_size' => envInt('REDIS_RED_LOCK_POOL_SIZE', 5),
                'passwd' => env('REDIS_RED_LOCK_PASSWD', null),
                'db' => envInt('REDIS_RED_LOCK_DB', 2),
                'prefix' => env('REDIS_RED_LOCK_PREFIX', 'sw-fw-less:'),
            ],
            'rate_limit' => [
                'host' => env('REDIS_RATE_LIMIT_HOST', '127.0.0.1'),
                'port' => envInt('REDIS_RATE_LIMIT_PORT', 6379),
                'timeout' => envDouble('REDIS_RATE_LIMIT_TIMEOUT', 1),
                'pool_size' => envInt('REDIS_RATE_LIMIT_POOL_SIZE', 5),
                'passwd' => env('REDIS_RATE_LIMIT_PASSWD', null),
                'db' => envInt('REDIS_RATE_LIMIT_DB', 3),
                'prefix' => env('REDIS_RATE_LIMIT_PREFIX', 'sw-fw-less:'),
            ],
        ],
        'switch' => envInt('REDIS_SWITCH', 0),
        'pool_change_event' => envInt('REDIS_POOL_CHANGE_EVENT', 0),
        'report_pool_change' => envInt('REDIS_REPORT_POOL_CHANGE', 0),
    ],

    //MySQL
    'mysql' => [
        'dsn' => env('MYSQL_DSN', 'mysql:dbname=sw_test;host=127.0.0.1'),
        'username' => env('MYSQL_USERNAME', 'root'),
        'passwd' => env('MYSQL_PASSWD', null),
        'options' => [
            \PDO::ATTR_CASE => \PDO::CASE_NATURAL,
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_ORACLE_NULLS => \PDO::NULL_NATURAL,
            \PDO::ATTR_STRINGIFY_FETCHES => false,
            \PDO::ATTR_EMULATE_PREPARES => false,
        ],
        'pool_size' => envInt('MYSQL_POOL_SIZE', 5),
        'switch' => envInt('MYSQL_SWITCH', 0),
        'pool_change_event' => envInt('MYSQL_POOL_CHANGE_EVENT', 0),
        'report_pool_change' => envInt('MYSQL_REPORT_POOL_CHANGE', 0),
    ],

    //Log
    'log' => [
        'path' => env('LOG_PATH', __DIR__ . '/../runtime/logs/app-{date}.log'),
        'level' => envInt('LOG_LEVEL', \Monolog\Logger::DEBUG),
        'pool_size' => envInt('LOG_POOL_SIZE', 100),
        'buffer_max_size' => envInt('LOG_BUFFER_MAX_SIZE', 10),
        'name' => env('LOG_NAME', 'sw-fw-less'),
        'reserve_days' => envInt('LOG_RESERVE_DAYS', 3),
        'switch' => envInt('LOG_SWITCH', 0),
    ],

    //Elasticsearch
    'elasticsearch' => [
        'connections' => [
            'default' => [
                'hosts' => [
                    env('ES_DEFAULT_HOST', '127.0.0.1:9200'),
                ],
                'timeout' => envDouble('ES_TIMEOUT', 1),
            ],
        ],
        'switch' => envInt('ES_SWITCH', 0),
    ],

    //Middleware
    'middleware' => [
        \App\components\zipkin\Middleware::class,
//        \App\middlewares\Cors::class,
//        \App\components\auth\Middleware::class,
    ],

    //Cors
    'cors' => [
        'origin' => env('CORS_ORIGIN', ''),
        'switch' => envInt('CORS_SWITCH', 0),
    ],

    //Storage
    'storage' => [
        'base_path' => env('APP_BASE_PATH', __DIR__ . '/../'),
        'switch' => envInt('STORAGE_SWITCH', 0),
        'storage_path' => env('STORAGE_PATH', __DIR__ . '/../runtime/storage/'),
        'types' => envArray('STORAGE_TYPES', ['file', 'qiniu', 'alioss']),
        'ext' => [
            'qiniu' => [
                'default_bucket' => env('QINIU_DEFAULT_BUCKET', 'default'),
                'buckets' => [
                    env('QINIU_DEFAULT_BUCKET', 'default') => [
                        'access_key' => env('QINIU_DEFAULT_ACCESS_KEY', ''),
                        'secret_key' => env('QINIU_DEFAULT_SECRET_KEY', ''),
                        'domain' => env('QINIU_DEFAULT_DOMAIN', ''),
                    ],
                ],
            ],
            'alioss' => [
                'default_bucket' => env('ALIOSS_DEFAULT_BUCKET', 'default'),
                'buckets' => [
                    env('ALIOSS_DEFAULT_BUCKET', 'default') => [
                        'access_id' => env('ALIOSS_DEFAULT_ACCESS_ID', ''),
                        'access_secret' => env('ALIOSS_DEFAULT_ACCESS_SECRET', ''),
                        'endpoint' => env('ALIOSS_DEFAULT_ENDPOINT', ''),
                        'timeout' => envDouble('ALIOSS_DEFAULT_TIMEOUT', 1),
                        'connectTimeout' => envDouble('ALIOSS_DEFAULT_CONNECT_TIMEOUT', 1),
                        'isCName' => envBool('ALIOSS_DEFAULT_IS_CNAME', false),
                        'securityToken' => env('ALIOSS_DEFAULT_SECURITY_TOKEN', null),
                        'domain' => env('ALIOSS_DEFAULT_DOMAIN', ''),
                    ],
                ],
            ],
        ],
    ],

    //Timezone
    'timezone' => env('TIMEZONE', 'PRC'),

    //Monitor
    'monitor' => [
        'switch' => envInt('MONITOR_SWITCH', 0),
    ],

    //AMQP
    'amqp' => [
        'pool_size' => envInt('AMQP_POOL_SIZE', 5),
        'switch' => envInt('AMQP_SWITCH', 0),
        'prefix' => env('AMQP_PREFIX', 'sw-fw-less:'),
        'channel_id' => envInt('AMQP_CHANNEL_ID', 1),
        'host' => env('AMQP_HOST', '127.0.0.1'),
        'port' => envInt('AMQP_PORT', 5672),
        'user' => env('AMQP_USER', 'guest'),
        'passwd' => env('AMQP_PASSWD', 'guest'),
        'vhost' => env('AMQP_VHOST', '/'),
        'locale' => env('AMQP_LOCALE', 'en_US'),
        'read_timeout' => envInt('AMQP_READ_TIMEOUT', 3),
        'keepalive' => envBool('AMQP_KEEPALIVE', false),
        'write_timeout' => envInt('AMQP_WRITE_TIMEOUT', 3),
        'heartbeat' => envInt('AMQP_HEARTBEAT', 0),
        'pool_change_event' => envInt('AMQP_POOL_CHANGE_EVENT', 0),
        'report_pool_change' => envInt('AMQP_REPORT_POOL_CHANGE', 0),
    ],

    //Events
    'events' => [
        'redis:pool:change' => [
            function (\Cake\Event\Event $event) {
                $count = $event->getData('count');

                if (\App\components\Config::get('redis.report_pool_change')) {
                    \App\components\utils\swoole\counter\Counter::incr('monitor:pool:redis', $count);
                }
            },
        ],
        'mysql:pool:change' => [
            function (\Cake\Event\Event $event) {
                $count = $event->getData('count');

                if (\App\components\Config::get('mysql.report_pool_change')) {
                    \App\components\utils\swoole\counter\Counter::incr('monitor:pool:mysql', $count);
                }
            },
        ],
        'amqp:pool:change' => [
            function (\Cake\Event\Event $event) {
                $count = $event->getData('count');

                if (\App\components\Config::get('amqp.report_pool_change')) {
                    \App\components\utils\swoole\counter\Counter::incr('monitor:pool:amqp', $count);
                }
            },
        ],
        'hbase:pool:change' => [
            function (\Cake\Event\Event $event) {
                $count = $event->getData('count');

                if (\App\components\Config::get('hbase.report_pool_change')) {
                    \App\components\utils\swoole\counter\Counter::incr('monitor:pool:hbase', $count);
                }
            },
        ],
    ],

    //Hbase
    'hbase' => [
        'pool_size' => envInt('HBASE_POOL_SIZE', 5),
        'switch' => envInt('HBASE_SWITCH', 0),
        'host' => env('HBASE_HOST', '127.0.0.1'),
        'port' => envInt('HBASE_PORT', 9090),
        'read_timeout' => envInt('HBASE_READ_TIMEOUT', 5000),
        'write_timeout' => envInt('HBASE_WRITE_TIMEOUT', 5000),
        'pool_change_event' => envInt('HBASE_POOL_CHANGE_EVENT', 0),
        'report_pool_change' => envInt('HBASE_REPORT_POOL_CHANGE', 0),
    ],

    //Throttle
    'throttle' => [
        'metric' => function(\App\components\http\Request $request){
            return $request->getRoute();
        },
        'period' => envInt('THROTTLE_PERIOD', 60),
        'throttle' => envInt('THROTTLE_THROTTLE', 10000),
    ],

    //Zipkin
    'zipkin' => [
        'service_name' => env('ZIPKIN_SERVICE_NAME', 'Sw-Fw-Less'),
        'endpoint_url' => env('ZIPKIN_ENDPOINT_URL', 'http://localhost:9411/api/v2/spans'),
        'sample_rate' => envInt('ZIPKIN_SAMPLE_RATE', 0),
        'body_size' => envInt('ZIPKIN_BODY_SIZE', 5000),
        'curl_timeout' => envInt('ZIPKIN_CURL_TIMEOUT', 1),
        'redis_options' => [
            'queue_name' => env('ZIPKIN_REDIS_QUEUE_NAME', 'queue:zipkin:span'),
            'connection' => env('ZIPKIN_REDIS_CONNECTION', 'zipkin'),
        ],
        'report_type' => env('ZIPKIN_REPORT_TYPE', 'http'),
    ],

    //RedLock
    'red_lock' => [
        'connection' => env('RED_LOCK_CONNECTION', 'red_lock'),
    ],

    //RateLimit
    'rate_limit' => [
        'connection' => env('RATE_LIMIT_CONNECTION', 'rate_limit'),
    ],

    //Providers
    'providers' => [
        //App Providers
        \App\components\datetime\DatetimeProvider::class,
        \App\components\utils\swoole\counter\CounterProvider::class,
        \App\components\event\EventProvider::class,

        //Request Providers
        \App\components\log\LogProvider::class,
        \App\components\redis\RedisProvider::class,
        \App\components\mysql\MysqlProvider::class,
        \App\components\es\EsProvider::class,
        \App\components\storage\StorageProvider::class,
        \App\components\amqp\AmqpProvider::class,
        \App\components\hbase\HbaseProvider::class,
    ],

    //Auth
    'auth' => [
        'guard' => 'token',
        'guards' => [
            'token' => [
                'guard' => \App\components\auth\token\Guard::class,
                'user_provider' => \App\models\User::class,
                'credential_key' => 'auth_token',
            ]
        ],
    ],
];
