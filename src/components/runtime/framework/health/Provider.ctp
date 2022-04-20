<?php

namespace SwFwLess\components\runtime\framework\health;

use SwFwLess\components\provider\WorkerProviderContract;
use SwFwLess\components\runtime\framework\health\probes\WorkerNumProbe;
use SwFwLess\components\swoole\Server;

class Provider implements WorkerProviderContract
{
    public static function bootWorker()
    {
        $serverInstance = Server::getInstance();
        $serverConfig = Server::config();

        HealthCheck::create($serverInstance, $serverConfig);
        WorkerNumProbe::create($serverInstance, $serverConfig);
    }

    public static function shutdownWorker()
    {
        //
    }
}
