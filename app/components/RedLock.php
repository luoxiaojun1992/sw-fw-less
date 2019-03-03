<?php

namespace App\components;

/**
 * Class RedLock
 *
 * {@inheritdoc}
 *
 * Redis实现分布式独占锁
 *
 * @package App\components
 */
class RedLock
{
    private $locked_keys = [];

    /**
     * @var RedisPool
     */
    private $redisPool;

    /**
     * @param RedisPool|null $redisPool
     * @return RedLock
     */
    public static function create(RedisPool $redisPool = null)
    {
        return new self($redisPool);
    }

    /**
     * RedLock constructor.
     * @param RedisPool|null $redisPool
     */
    public function __construct(RedisPool $redisPool = null)
    {
        $this->redisPool = $redisPool;
    }

    /**
     * 加独占锁
     *
     * {@inheritdoc}
     *
     * 通过__call魔术方法调用，勿删除
     *
     * @param     $key
     * @param     int $ttl
     * @param     bool $guard 是否自动释放
     * @redis_key
     * @return    bool
     * @throws \Exception
     */
    public function lock($originKey, $ttl = 0, $guard = false)
    {
        $key = $this->redisPool->getKey($originKey);
        /** @var \Redis $redis */
        $redis = $this->redisPool->pick();
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
                $this->addLockedKey($originKey, $guard);
                return true;
            }
        } catch (\Exception $e) {
            throw $e;
        } finally {
            $this->redisPool->release($redis);
        }

        return false;
    }

    /**
     * 释放独占锁
     *
     * {@inheritdoc}
     *
     * 通过__call魔术方法调用，勿删除
     *
     * @param     $key
     * @redis_key
     * @return    bool
     * @throws \Exception
     */
    public function unlock($originKey)
    {
        $key = $this->redisPool->getKey($originKey);
        /** @var \Redis $redis */
        $redis = $this->redisPool->pick();
        try {
            $result = $redis->del($key);
            if ($result > 0) {
                unset($this->locked_keys[$originKey]);
                return true;
            }
            return false;
        } catch (\Exception $e) {
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
     * 清除所有锁
     */
    public function flushAll()
    {
        foreach ($this->locked_keys as $locked_key) {
            if (!$locked_key['guard']) {
                $this->unlock($locked_key['key']);
            }
        }
    }

    public function __destruct()
    {
        $this->flushAll();
    }
}
