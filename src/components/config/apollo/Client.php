<?php

namespace SwFwLess\components\config\apollo;

use SwFwLess\components\swoole\Scheduler;

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
        $hasNotification = false;

        $hostInfo = parse_url($this->configServer);
        $schema = $hostInfo['scheme'] ?? 'http';
        $ssl = $schema === 'https';
        $host = $hostInfo['host'] ?? $this->configServer;
        $port = $hostInfo['port'] ?? ($ssl ? 443 : 80);

        $path = '/notifications/v2';
        $args = [];
        $args['appId'] = $this->appId;
        $args['cluster'] = $this->cluster;
        $args['notifications'] = json_encode([
            [
                'namespaceName' => $this->namespace,
                'notificationId' => $notificationId,
            ]
        ], JSON_UNESCAPED_UNICODE);
        $path .= ('?' . http_build_query($args));

        $httpClient = new \Swoole\Coroutine\Http\Client($host, $port, $ssl);
        $httpClient->set(['timeout' => $this->notificationInterval]);
        $httpClient->get($path);
        $statusCode = $httpClient->getStatusCode();
        $body = $statusCode === 200 ? $httpClient->getBody() : null;
        $httpClient->close();

        if ($statusCode === 200) {
            $result = json_decode($body, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                if (isset($result[0]['notificationId'])) {
                    $newNotificationId = $result[0]['notificationId'];
                    $hasNotification = Scheduler::withoutPreemptive(function () use (&$notificationId, $newNotificationId) {
                        $hasNotification = ($notificationId !== -1) && ($newNotificationId !== $notificationId);
                        if ($newNotificationId !== $notificationId) {
                            $notificationId = $newNotificationId;
                        }
                        return $hasNotification;
                    });
                }
            }
        }

        return $hasNotification;
    }
}
