<?php

namespace SwFwLess\components\config\parsers;

use SwFwLess\components\config\apollo\ClientBuilder;

class Apollo
{
    public static function parse($config)
    {
        $apolloClientBuilder = ClientBuilder::create()
            ->setConfigServer($config['config_server'])
            ->setAppId($config['app_id'])
            ->setCluster($config['cluster'])
            ->setClientIp($config['client_ip'])
            ->setPullTimeout($config['pull_timeout'])
            ->setNamespace($config['namespace'])
            ->setReleaseKey($config['release_key']);
        $apolloClient = $apolloClientBuilder->build();
        $config = $apolloClient->pullConfig();
        return $config['configurations'] ?? [];
    }
}
