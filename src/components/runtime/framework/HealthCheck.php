<?php

namespace SwFwLess\components\runtime\framework;

use SwFwLess\components\runtime\framework\health\ProbeContract;
use SwFwLess\components\runtime\framework\health\probes\WorkerNumProbe;

class HealthCheck
{
    /**
     * @var \Swoole\Http\Server
     */
    protected $swServer;

    protected $serverConfig;

    /**
     * @var ProbeContract[]
     */
    protected $probes = [];

    public static function create($swServer, $serverConfig)
    {
        return (new static())->setSwServer($swServer)
            ->setServerConfig($serverConfig);
    }

    public function __construct()
    {
        $this->registerDefaultProbes();
    }

    protected function registerDefaultProbes()
    {
        //todo injected from construct method
        $this->registerProbes([
            WorkerNumProbe::create($this->swServer, $this->serverConfig)
        ]);
    }

    /**
     * @return \Swoole\Http\Server
     */
    public function getSwServer(): \Swoole\Http\Server
    {
        return $this->swServer;
    }

    /**
     * @param \Swoole\Http\Server $swServer
     * @return $this
     */
    public function setSwServer(\Swoole\Http\Server $swServer)
    {
        $this->swServer = $swServer;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getServerConfig()
    {
        return $this->serverConfig;
    }

    /**
     * @param $serverConfig
     * @return $this
     */
    public function setServerConfig($serverConfig)
    {
        $this->serverConfig = $serverConfig;
        return $this;
    }

    /**
     * @param $probe
     * @return $this
     */
    public function registerProbe($probe)
    {
        $this->probes[] = $probe;
        return $this;
    }

    /**
     * @param $probes
     * @return $this
     */
    public function registerProbes($probes)
    {
        foreach ($probes as $probe) {
            $this->registerProbe($probe);
        }
        return $this;
    }

    /**
     * @return bool
     */
    public function status()
    {
        foreach ($this->probes as $probe) {
            if (!$probe->health()) {
                return false;
            }
        }

        return true;
    }

    /**
     * @deprecated
     * @return bool
     */
    protected function checkWorkerNum()
    {
        return ($this->swServer->stats()['worker_num']) === ($this->serverConfig['worker_num']);
    }
}
