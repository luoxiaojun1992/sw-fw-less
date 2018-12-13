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
        $clientBuilder = ClientBuilder::create();
        $clientBuilder->setHosts($this->config['connections'][$connection_name]['hosts']);
        $clientBuilder->setLogger(Log::getLogger());
        $clientBuilder->setHandler(new GuzzleCoHandler(['timeout' => 1]));
        return $clientBuilder->build();
    }
}
