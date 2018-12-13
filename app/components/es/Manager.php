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

    protected $connections;
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

        return self::$instance = new self();
    }

    /**
     * 获取ES连接
     *
     * @param  $connection_name
     * @return Client|null
     */
    public function connection($connection_name = 'default')
    {
        return isset($this->connections[$connection_name]) ?
            $this->connections[$connection_name] :
            $this->addConnection($connection_name);
    }

    /**
     * 添加ES连接
     *
     * @param  $connection_name
     * @return Client|null
     */
    protected function addConnection($connection_name = 'default')
    {
        if (isset($this->connections[$connection_name])) {
            return $this->connections[$connection_name];
        }

        $connections_config = $this->config['connections'];
        if (isset($connections_config[$connection_name])) {
            $clientBuilder = ClientBuilder::create();
            $clientBuilder->setHosts($connections_config[$connection_name]['hosts']);
            $clientBuilder->setLogger(Log::getLogger());
            $clientBuilder->setHandler(new GuzzleCoHandler());
            return $this->connections[$connection_name] = $clientBuilder->build();
        }

        return null;
    }
}
