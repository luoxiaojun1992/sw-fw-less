<?php

$serverConfig = [
    'host' => env('SERVER_HOST', '0.0.0.0'),
    'port' => envInt('SERVER_PORT', 9501),
    'reactor_num' => envInt('SERVER_REACTOR_NUM', 8),
    'worker_num' => envInt('SERVER_WORKER_NUM', 32),
    'daemonize' => envBool('SERVER_DAEMONIZE', false),
    'backlog' => envInt('SERVER_BACKLOG', 128),
    'max_request' => envInt('SERVER_MAX_REQUEST', 0),
    'dispatch_mode' => envInt('SERVER_DISPATCH_MODE', 2),
];

if (!empty($pidFile = env('SERVER_PID_FILE'))) {
    $serverConfig['pid_file'] = $pidFile;
}

return $serverConfig;
