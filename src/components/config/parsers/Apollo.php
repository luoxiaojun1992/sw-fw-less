<?php

namespace SwFwLess\components\config\parsers;

use SwFwLess\components\config\apollo\ClientBuilder;

class Apollo
{
    public static function parse($config)
    {
        $apolloConfig = $config['apollo'];
        $apolloClientBuilder = ClientBuilder::create()
            ->setConfigServer($apolloConfig['config_server'])
            ->setAppId($apolloConfig['app_id'])
            ->setCluster($apolloConfig['cluster'])
            ->setClientIp($apolloConfig['client_ip'])
            ->setPullTimeout($apolloConfig['pull_timeout'])
            ->setNamespace($apolloConfig['namespace'])
            ->setReleaseKey($apolloConfig['release_key']);
        $apolloClient = $apolloClientBuilder->build();
        $remoteConfig = $apolloClient->pullConfig();
        return $remoteConfig['configurations'] ?? [];
    }
}
