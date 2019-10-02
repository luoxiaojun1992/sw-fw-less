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

        $api = rtrim($this->configServer, '/') . '/configs/' . $this->appId . '/' . $this->cluster . '/' .
            $this->namespace;
        $args = [];
        $args['ip'] = $this->clientIp;
        $args['releaseKey'] = $this->releaseKey;
        $api .= ('?' . http_build_query($args));

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $api);
        curl_setopt($ch, CURLOPT_VERBOSE, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->pullTimeout);
        $body = curl_exec($ch);
        if (curl_getinfo($ch, CURLINFO_HTTP_CODE) === 200) {
            $config = json_decode($body, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $config = [];
            }
        }
        curl_close($ch);

        return $config;
    }

    public function notification(&$notificationId)
    {
        //todo
        return false;
    }
}
