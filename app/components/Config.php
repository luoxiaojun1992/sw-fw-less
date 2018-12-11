<?php

namespace App\components;

class Config
{
    private static $config = [
        //Server
        'server' => [
            'host' => '127.0.0.1',
            'port' => 9501,
            'reactor_num' => 8,
            'worker_num' => 32,
            'daemonize' => false,
            'backlog' => 128,
            'max_request' => 0,
        ],

        //Redis
        'redis' => [
            'host' => '127.0.0.1',
            'port' => 6379,
            'timeout' => 1,
            'pool_size' => 5,
            'passwd' => null,
            'db' => 0,
        ],

        //MySQL
        'mysql' => [
            'dsn' => 'mysql:dbname=sw_test;host=127.0.0.1',
            'username' => 'root',
            'passwd' => null,
            'options' => [
                \PDO::ATTR_CASE => \PDO::CASE_NATURAL,
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_ORACLE_NULLS => \PDO::NULL_NATURAL,
                \PDO::ATTR_STRINGIFY_FETCHES => false,
                \PDO::ATTR_EMULATE_PREPARES => false,
            ],
            'pool_size' => 5,
        ],
    ];

    /**
     * @param $key
     * @return array|mixed|null
     */
    public static function get($key)
    {
        if (!is_string($key)) {
            return null;
        }
        if (!$key) {
            return null;
        }

        $arr = self::$config;
        $keys = explode('.', $key);
        foreach ($keys as $key) {
            if (isset($arr[$key])) {
                $arr = $arr[$key];
            } else {
                $arr = null;
            }
        }

        return $arr;
    }
}
