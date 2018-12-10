<?php

namespace App\components;

class Redis
{
    private static $instance;

    /** @var \Redis[] */
    private $redisPool = [];

    private $host;
    private $port;
    private $timeout;
    private $poolSize;

    public static function create($host = '127.0.0.1', $port = 6379, $timeout = 1, $poolSize = 100)
    {
        if (self::$instance instanceof self) {
            return self::$instance;
        }

        return self::$instance = new self($host, $port, $timeout, $poolSize);
    }

    /**
     * Redis constructor.
     * @param $host
     * @param $port
     * @param $timeout
     * @param $poolSize
     */
    public function __construct($host, $port, $timeout, $poolSize)
    {
        $this->host = $host;
        $this->port = $port;
        $this->timeout = $timeout;
        $this->poolSize = $poolSize;
    }

    /**
     * @return \Redis mixed
     */
    public function pick()
    {
        $redis = array_pop($this->redisPool);
        if (!$redis && count($this->redisPool) < $this->poolSize) {
            $redis = $this->getConnect();
        }

        return $redis;
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

    /**
     * @return \Redis
     */
    private function getConnect()
    {
        $redis = new \Redis();
        $redis->connect($this->host, $this->port, $this->timeout);
        return $redis;
    }
}
