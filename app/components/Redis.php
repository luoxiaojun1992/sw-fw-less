<?php

namespace App\components;

class Redis
{
    private static $instance;

    /** @var \Redis[] */
    private $redisPool;

    public static function create($host = '127.0.0.1', $port = 6379, $timeout = 1, $count = 100)
    {
        if (self::$instance instanceof self) {
            return self::$instance;
        }

        return self::$instance = new self($host, $port, $timeout, $count);
    }

    /**
     * Redis constructor.
     * @param $host
     * @param $port
     * @param $timeout
     * @param $count
     */
    public function __construct($host, $port, $timeout, $count)
    {
        for ($i = 0; $i < $count; ++$i) {
            $redis = new \Redis();
            $redis->connect($host, $port, $timeout);
            $this->redisPool[] = $redis;
        }
    }

    /**
     * @return \Redis mixed
     */
    public function pick()
    {
        return array_pop($this->redisPool);
    }

    /**
     * @param $redis
     */
    public function release($redis)
    {
        $this->redisPool[] = $redis;
    }

    public function __destruct()
    {
        foreach ($this->redisPool as $redis) {
            $redis->close();
        }
    }
}
