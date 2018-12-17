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
    private $prefix = 'sw-fw-less:';

    /**
     * @param string $host
     * @param int $port
     * @param int $timeout
     * @param int $poolSize
     * @param null $passwd
     * @param int $db
     * @param string $prefix
     * @return RedisPool|null
     */
    public static function create(
        $host = '127.0.0.1',
        $port = 6379,
        $timeout = 1,
        $poolSize = 100,
        $passwd = null,
        $db = 0,
        $prefix = 'sw-fw-less:'
    )
    {
        if (self::$instance instanceof self) {
            return self::$instance;
        }

        if (Config::get('redis.switch')) {
            return self::$instance = new self($host, $port, $timeout, $poolSize, $passwd, $db, $prefix);
        } else {
            return null;
        }
    }

    /**
     * Redis constructor.
     * @param $host
     * @param $port
     * @param $timeout
     * @param $poolSize
     * @param $passwd
     * @param $db
     * @param $prefix
     */
    public function __construct($host, $port, $timeout, $poolSize, $passwd, $db, $prefix = 'sw-fw-less:')
    {
        $this->host = $host;
        $this->port = $port;
        $this->timeout = $timeout;
        $this->poolSize = $poolSize;
        $this->passwd = $passwd;
        $this->db = $db;
        $this->prefix = $prefix;

        for ($i = 0; $i < $poolSize; ++$i) {
            $this->redisPool[] = $this->getConnect();
        }

        RedisStreamWrapper::register();
    }

    /**
     * @param $key
     * @return string
     */
    public function getKey($key)
    {
        return $this->prefix . $key;
    }

    /**
     * @return RedisWrapper mixed
     */
    public function pick()
    {
        $redis = array_pop($this->redisPool);
        if (!$redis) {
            $redis = $this->getConnect(false);
        }

        return $redis;
    }

    /**
     * @param RedisWrapper $redis
     */
    public function release($redis)
    {
        if ($redis) {
            if ($redis->inTransaction()) {
                try {
                    $redis->discard();
                } catch (\RedisException $e) {
                    if ($redis->isNeedRelease()) {
                        $redis = $this->handleRollbackException($redis, $e);
                    }
                }
            }
            if ($redis->isNeedRelease()) {
                $this->redisPool[] = $redis;
            }
        }
    }

    public function __destruct()
    {
        foreach ($this->redisPool as $redis) {
            $redis->close();
        }
    }

    /**
     * @param bool $needRelease
     * @return RedisWrapper
     */
    public function getConnect($needRelease = true)
    {
        $redis = new \Redis();
        $redis->connect($this->host, $this->port, $this->timeout);
        if ($this->passwd) {
            $redis->auth($this->passwd);
        }
        $redis->select($this->db);
        return (new RedisWrapper())->setRedis($redis)->setNeedRelease($needRelease);
    }

    /**
     * @param RedisWrapper $redis
     * @param \RedisException $e
     * @return RedisWrapper
     */
    public function handleRollbackException($redis, \RedisException $e)
    {
        if (Helper::causedByLostConnection($e)) {
            $redis = $this->getConnect();
        }

        return $redis;
    }

    /**
     * @return int
     */
    public function countPool()
    {
        return count($this->redisPool);
    }
}
