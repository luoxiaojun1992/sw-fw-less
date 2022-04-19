<?php

namespace SwFwLess\components\runtime\framework\health\probes;

use SwFwLess\components\runtime\framework\health\ProbeContract;

class WorkerNumProbe implements ProbeContract
{
    /**
     * @var \Swoole\Http\Server
     */
    protected $swServer;

    protected $serverConfig;

    public static function create($swServer, $serverConfig)
    {
        return new static($swServer, $serverConfig);
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
