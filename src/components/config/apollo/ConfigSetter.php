<?php

namespace SwFwLess\components\config\apollo;

trait ConfigSetter
{
    protected $configServer;

    protected $appId;

    protected $cluster = 'default';

    protected $clientIp = '127.0.0.1';

    protected $pullTimeout = 10;

    protected $namespace;

    protected $releaseKey = '';

    /**
     * @param $configServer
     * @return $this
     */
    public function setConfigServer($configServer)
    {
        $this->configServer = $configServer;
        return $this;
    }

    /**
     * @param $appId
     * @return $this
     */
    public function setAppId($appId)
    {
        $this->appId = $appId;
        return $this;
    }

    /**
     * @param string $cluster
     * @return $this
     */
    public function setCluster(string $cluster)
    {
        $this->cluster = $cluster;
        return $this;
    }

    /**
     * @param string $clientIp
     * @return $this
     */
    public function setClientIp(string $clientIp)
    {
        $this->clientIp = $clientIp;
        return $this;
    }

    /**
     * @param int $pullTimeout
     * @return $this
     */
    public function setPullTimeout(int $pullTimeout)
    {
        $this->pullTimeout = $pullTimeout;
        return $this;
    }

    /**
     * @param $namespace
     * @return $this
     */
    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;
        return $this;
    }

    /**
     * @param string $releaseKey
     * @return $this
     */
    public function setReleaseKey(string $releaseKey)
    {
        $this->releaseKey = $releaseKey;
        return $this;
    }
}
