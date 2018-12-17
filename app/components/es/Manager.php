<?php

namespace App\components\es;

use App\components\Config;
use App\components\GuzzleCoHandler;
use App\facades\Log;
use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;

/**
 * Class Manager
 *
 * {@inheritdoc}
 *
 * ES连接管理
 *
 * @package Lxj\Laravel\Elasticsearch
 */
class Manager
{
    private static $instance;

    protected $config;

    public function __construct()
    {
        $this->config = Config::get('elasticsearch');
    }

    public static function create()
    {
        if (self::$instance instanceof self) {
            return self::$instance;
        }

        if (Config::get('elasticsearch.switch')) {
            return self::$instance = new self();
        } else {
            return null;
        }
    }

    /**
     * 获取ES连接
     *
     * @param  $connection_name
     * @return Client|null
     */
    public function connection($connection_name = 'default')
    {
        $clientBuilder = ClientBuilder::create();
        $clientBuilder->setHosts($this->config['connections'][$connection_name]['hosts']);
        $clientBuilder->setLogger(Log::getLogger());
        if (extension_loaded('swoole')) {
            $clientBuilder->setHandler(new GuzzleCoHandler(['timeout' => $this->config['connections'][$connection_name]['timeout']]));
        }
        return $clientBuilder->build();
    }
}
