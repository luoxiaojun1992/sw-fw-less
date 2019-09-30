<?php

namespace SwFwLess\components\config\apollo;

class Client
{
    use ConfigSetter;

    public static function create()
    {
        return new static();
    }

    public function __construct()
    {
        //
    }

    public function pullConfig()
    {
        $config = [];

        $serverInfo = parse_url($this->configServer);
        $schema = $serverInfo['scheme'] ?? 'http';
        $ssl = $schema === 'https';
        $host = $serverInfo['host'] ?? $this->configServer;
        $port = $serverInfo['port'] ?? ($ssl ? 443 : 80);
        $httpClient = new \Swoole\Coroutine\Http\Client($host, $port, $ssl);

        $path = '/configs/' . $this->appId . '/' . $this->cluster . '/';
        $path = $path . $this->namespace;
        $args = [];
        $args['ip'] = $this->clientIp;
        $args['releaseKey'] = $this->releaseKey;
        $path .= ('?' . http_build_query($args));

        $httpClient->setMethod(['timeout' => $this->pullTimeout]);
        $httpClient->get($path);
        if ($httpClient->getStatusCode() === 200) {
            $config = json_decode($httpClient->getBody(), true);
        }
        $httpClient->close();

        return $config;
    }
}
