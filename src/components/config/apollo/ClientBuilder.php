<?php

namespace SwFwLess\components\config\apollo;

class ClientBuilder
{
    use ConfigSetter;

    public static function create()
    {
        return new static();
    }

    public function build()
    {
        return (new Client())->setConfigServer($this->configServer)
            ->setAppId($this->appId)
            ->setCluster($this->cluster)
            ->setClientIp($this->clientIp)
            ->setPullTimeout($this->pullTimeout)
            ->setNamespace($this->namespace);
    }
}
