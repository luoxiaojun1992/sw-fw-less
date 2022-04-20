<?php

namespace SwFwLess\components\runtime\framework\health\probes;

use SwFwLess\components\runtime\framework\health\ProbeContract;
use SwFwLess\components\traits\SingletonInstance;

class WorkerNumProbe implements ProbeContract
{
    use SingletonInstance;

    /**
     * @var \Swoole\Http\Server
     */
    protected $swServer;

    protected $serverConfig;

    public static function create($swServer = null, $serverConfig = [])
    {
        return static::fetchOrCreateInstance(function () use ($swServer, $serverConfig) {
            return new static($swServer, $serverConfig);
        });
    }

    public function __construct($swServer, $serverConfig)
    {
        $this->swServer = $swServer;
        $this->serverConfig = $serverConfig;
    }

    public function health(): bool
    {
        return ($this->swServer->stats()['worker_num']) === ($this->serverConfig['worker_num']);
    }
}
