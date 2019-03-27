<?php

namespace App\components\redis;

/**
 * Class RedLock
 *
 * {@inheritdoc}
 *
 * Distributed lock based on redis
 *
 * @package App\components\redis
 */
class RedLock
{
    private $locked_keys = [];

    /**
     * @var RedisPool
     */
    private $redisPool;

    private $config = ['connection' => 'red_lock'];

    /**
     * @param RedisPool|null $redisPool
     * @param array $config
     * @return RedLock
     */
    public static function create(RedisPool $redisPool = null, $config = [])
    {
        return new self($redisPool, $config);
    }

    /**
     * RedLock constructor.
     * @param RedisPool|null $redisPool
     * @param array $config
     */
    public function __construct(RedisPool $redisPool = null, $config = [])
    {
        $this->redisPool = $redisPool;
        $this->config = array_merge($this->config, $config);
    }

    /**
     * Add a lock
     *
     * @param     $key
     * @param     int $ttl
     * @param     bool $guard
     * @return    bool
     * @throws \Throwable
     */
    public function lock($key, $ttl = 0, $guard = false)
    {
        /** @var \Redis $redis */
        $redis = $this->redisPool->pick($this->config['connection']);
        try {
            //因为redis整数对象有缓存，此处value使用1
            if ($ttl > 0) {
                $lua = <<<EOF
local new_value=redis.call('setnx', KEYS[1], ARGV[1]);
if(new_value > 0) then 
redis.call('expire', KEYS[1], ARGV[2]) 
end
return new_value
EOF;
                $result = $redis->eval($lua, [$key, 1, $ttl], 1);
            } else {
                $result = $redis->setnx($key, 1);
            }
            if ($result > 0) {
                $this->addLockedKey($key, $guard);
                return true;
            }
        } catch (\Throwable $e) {
            throw $e;
        } finally {
            $this->redisPool->release($redis);
        }

        return false;
    }

    /**
     * Release a lock
     *
     * @param     $key
     * @return    bool
     * @throws \Throwable
     */
    public function unlock($key)
    {
        /** @var \Redis $redis */
        $redis = $this->redisPool->pick($this->config['connection']);
        try {
            $result = $redis->del($key);
            if ($result > 0) {
                unset($this->locked_keys[$key]);
                return true;
            }
            return false;
        } catch (\Throwable $e) {
            throw $e;
        } finally {
            $this->redisPool->release($redis);
        }
    }

    private function addLockedKey($key, $guard = false)
    {
        $this->locked_keys[$key] = [
            'key' => $key,
            'guard' => $guard,
        ];
    }

    /**
     * Flush all locks
     * @throws \Throwable
     */
    public function flushAll()
    {
        foreach ($this->locked_keys as $locked_key) {
            if (!$locked_key['guard']) {
                $this->unlock($locked_key['key']);
            }
        }
    }

    /**
     * @throws \Throwable
     */
    public function __destruct()
    {
        $this->flushAll();
    }
}
