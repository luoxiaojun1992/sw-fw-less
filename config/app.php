<?php

$config = [
    //Cors
    'cors' => [
        'origin' => env('CORS_ORIGIN', ''),
        'switch' => envInt('CORS_SWITCH', 0),
    ],

    //Timezone
    'timezone' => env('TIMEZONE', 'PRC'),

    //Monitor
    'monitor' => [
        'switch' => envInt('MONITOR_SWITCH', 0),
    ],

    //Throttle
    'throttle' => [
        'metric' => function(\App\components\http\Request $request){
            return $request->getRoute();
        },
        'period' => envInt('THROTTLE_PERIOD', 60),
        'throttle' => envInt('THROTTLE_THROTTLE', 10000),
    ],

    //RedLock
    'red_lock' => [
        'connection' => env('RED_LOCK_CONNECTION', 'red_lock'),
    ],

    //RateLimit
    'rate_limit' => [
        'connection' => env('RATE_LIMIT_CONNECTION', 'rate_limit'),
    ],

    //Cache
    'cache' => [
        'connection' => env('CACHE_CONNECTION', 'cache'), //redis connection
        'update_lock_ttl' => envInt('CACHE_UPDATE_LOCK_TTL', 10),
    ],

    //Hot Reload
    'hot_reload' => [
        'watch_dirs' => [
            __DIR__ . '/',
            __DIR__ . '/../app/',
            __DIR__ . '/../bootstrap/',
            __DIR__ . '/../vendor/',
            __DIR__ . '/../',
        ],
        'excluded_dirs' => [],
        'watch_suffixes' => ['.php', '.env'],
        'driver' => env('HOT_RELOAD_DRIVER', \Kwf\FileWatcher\Watcher::class), //HuangYi\Watcher\Watcher::class is another choice
    ],

    //Error handler
    'error_handler' => [
        'err_formatter' => function (\Throwable $e) {
            return nl2br($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        },
    ],
];

$fd = opendir(__DIR__);
while($file = readdir($fd)) {
    if (!in_array($file, ['.', '..', 'app.php'])) {
        $configName = substr($file, 0, -4);
        if (isset($config[$configName])) {
            $config[$configName] = array_merge($config[$configName], require __DIR__ . '/' . $file);
        } else {
            $config[$configName] = require __DIR__ . '/' . $file;
        }
    }
}
closedir($fd);

return $config;
