<?php

namespace SwFwLess\components\runtime\framework;

class HealthCheck
{
    /**
     * @var \Swoole\Http\Server
     */
    protected $swServer;

    protected $serverConfig;

    protected $probes = [];

    public static function create($swServer, $serverConfig)
    {
        return (new static())->setSwServer($swServer)
            ->setServerConfig($serverConfig);
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
        //todo support registering monitors
        if (!$this->checkWorkerNum()) {
            return false;
        }

        return true;
    }

    protected function checkWorkerNum()
    {
        return ($this->swServer->stats()['worker_num']) === ($this->serverConfig['worker_num']);
    }
}
