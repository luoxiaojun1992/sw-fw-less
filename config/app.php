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
];

$fd = opendir(__DIR__);
while($file = readdir($fd)) {
    if (!in_array($file, ['.', '..', 'app.php'])) {
        $configName = substr($file, 0, -4);
        if (isset($config[$configName])) {
            $config[$configName] = array_merge($config[$configName], require_once __DIR__ . '/' . $file);
        } else {
            $config[$configName] = require_once __DIR__ . '/' . $file;
        }
    }
}
closedir($fd);

return $config;
