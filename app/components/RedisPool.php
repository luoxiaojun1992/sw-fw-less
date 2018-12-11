<?php

namespace App\components;

class RedisPool
{
    private static $instance;

    /** @var RedisWrapper[] */
    private $redisPool = [];

    private $host;
    private $port;
    private $timeout;
    private $poolSize;
    private $passwd;
    private $db = 0;

    public static function create($host = '127.0.0.1', $port = 6379, $timeout = 1, $poolSize = 100, $passwd = null, $db = 0)
    {
        if (self::$instance instanceof self) {
            return self::$instance;
        }

        return self::$instance = new self($host, $port, $timeout, $poolSize, $passwd, $db);
    }

    /**
     * Redis constructor.
     * @param $host
     * @param $port
     * @param $timeout
     * @param $poolSize
     * @param $passwd
     * @param $db
     */
    public function __construct($host, $port, $timeout, $poolSize, $passwd, $db)
    {
        $this->host = $host;
        $this->port = $port;
        $this->timeout = $timeout;
        $this->poolSize = $poolSize;
        $this->passwd = $passwd;
        $this->db = $db;

        for ($i = 0; $i < $poolSize; ++$i) {
            $this->redisPool[] = $this->getConnect();
        }
    }

    /**
     * @return RedisWrapper mixed
     */
    public function pick()
    {
        return array_pop($this->redisPool);
    }

    /**
     * @param RedisWrapper $redis
     */
    public function release($redis)
    {
        if ($redis->inTransaction()) {
            $redis->discard();
        }
        $this->redisPool[] = $redis;
    }

    public function __destruct()
    {
        foreach ($this->redisPool as $redis) {
            $redis->close();
        }
    }

    /**
     * @return RedisWrapper
     */
    private function getConnect()
    {
        $redis = new \Redis();
        $redis->connect($this->host, $this->port, $this->timeout);
        if ($this->passwd) {
            $redis->auth($this->passwd);
        }
        $redis->select($this->db);
        return (new RedisWrapper())->setRedis($redis);
    }
}
