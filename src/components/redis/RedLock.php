<?php

namespace SwFwLess\components\redis;

use SwFwLess\components\swoole\coresource\traits\CoroutineRes;

/**
 * Class RedLock
 *
 * {@inheritdoc}
 *
 * Distributed lock based on redis
 *
 * @package SwFwLess\components\redis
 */
class RedLock
{
    use CoroutineRes;

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
        if ($instance = self::fetch()) {
            return $instance;
        }

        if (!is_null($redisPool)) {
            return new self($redisPool, $config);
        }

        return null;
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
        self::register($this);
    }

    /**
     * Add a lock
     *
     * @param     $key
     * @param     int $ttl
     * @param     bool $guard
     * @param     callable|null $callback
     * @return    mixed
     * @throws \Throwable
     */
    public function lock($key, $ttl = 0, $guard = false, $callback = null)
    {
        $deferTimerId = null;
        /** @var \Redis $redis */
        $redis = $this->redisPool->pick($this->config['connection']);
        try {
            //因为redis整数对象有缓存，此处value使用1
            if ($ttl > 0) {
                $result = $redis->set($key, 1, ['NX', 'EX' => $ttl]);
            } else {
                $result = $redis->setnx($key, 1);
            }
            if ($result) {
                $this->addLockedKey($key, $guard);

                if (is_callable($callback)) {
                    //Defer
                    if ($ttl >= 2) {
                        $deferTimerId = swoole_timer_tick(1000, function () use ($key, $ttl) {
                            /** @var \Redis $redis */
                            $redis = $this->redisPool->pick($this->config['connection']);
                            try {
                                $lua = <<<EOF
local existed=redis.call('exists', KEYS[1]);
if(existed >= 1) then
local remainTtl=redis.call('ttl', KEYS[1]);
if(remainTtl <= 1) then
redis.call('expire', KEYS[1], ARGV[1]);
end
end
EOF;
                                $redis->eval($lua, [$key, $ttl], 1);
                            } catch (\Throwable $e) {
                                throw $e;
                            } finally {
                                $this->redisPool->release($redis);
                            }
                        });
                    }

                    $callbackRes = call_user_func($callback);
                    $this->unlock($key);
                    return $callbackRes;
                }

                return true;
            }
        } catch (\Throwable $e) {
            throw $e;
        } finally {
            $this->redisPool->release($redis);
            if (!is_null($deferTimerId)) {
                swoole_timer_clear($deferTimerId);
            }
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
        if (!empty($this->locked_keys[$key]['guard'])) {
            return false;
        }

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

    /**
     * Defer a lock
     *
     * @param $key
     * @param $ttl
     * @return bool
     * @throws \RedisException
     * @throws \Throwable
     */
    public function defer($key, $ttl)
    {
        /** @var \Redis $redis */
        $redis = $this->redisPool->pick($this->config['connection']);
        try {
            return $redis->expire($key, $ttl);
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
