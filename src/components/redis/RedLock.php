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

    private $shared_locked_keys = [];

    /**
     * @var RedisPool
     */
    private $redisPool;

    private $config = [
        'connection' => 'red_lock',
        'lock_prefix' => 'lock:',
        'shared_lock_prefix' => 'shared:lock:',
    ];

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
     * @param RedisPool $redisPool
     * @param array $config
     */
    public function __construct(RedisPool $redisPool, $config = [])
    {
        $this->redisPool = $redisPool;
        $this->config = array_merge($this->config, $config);
        self::register($this);
    }

    protected function lockPrefix()
    {
        return $this->config['lock_prefix'];
    }

    protected function lockKeyWithPrefix($lockKey)
    {
        return $this->lockPrefix() . $lockKey;
    }

    protected function sharedLockPrefix()
    {
        return $this->config['shared_lock_prefix'];
    }

    protected function sharedLockKeyWithPrefix($lockKey)
    {
        return $this->sharedLockPrefix() . $lockKey;
    }

    /**
     * @param $key
     * @param false $guard
     * @param null $callback
     * @return bool|mixed
     * @throws \RedisException
     * @throws \Throwable
     */
    public function sharedLock($key, $guard = false, $callback = null)
    {
        $keyWithPrefix = $this->sharedLockKeyWithPrefix($key);

        /** @var \Redis $redis */
        $redis = $this->redisPool->pick($this->config['connection']);
        try {
            $lua = <<<EOF
local result = true;
local existed=redis.call('exists', KEYS[1]);
if(existed >= 1) then
    local counter=redis.call('get', KEYS[1])
    if(counter > -1) then
        local addCounterRes=redis.call('incr', KEYS[1])
        if(addCounterRes < 1) then
            result=false
        end
    end
    if(counter <= -1) then
        result=false
    end
else
    local initCounterRes=redis.call('set', KEYS[1], 1)
    if(not( initCounterRes )) then
        result=false
    end
end
return result
EOF;
            $result = $redis->eval($lua, [$keyWithPrefix], 1);
            if ($result) {
                $this->addSharedLockedKey($key, $guard);

                if (is_callable($callback)) {
                    $callbackRes = call_user_func($callback);
                    $this->sharedUnLock($key);
                    return $callbackRes;
                } else {
                    return true;
                }
            } else {
                return false;
            }
        } catch (\Throwable $e) {
            throw $e;
        } finally {
            $this->redisPool->release($redis);
        }
    }

    /**
     * @param $key
     * @return bool
     * @throws \RedisException
     * @throws \Throwable
     */
    public function sharedUnLock($key)
    {
        if (!empty($this->shared_locked_keys[$key]['guard'])) {
            return false;
        }

        $keyWithPrefix = $this->sharedLockKeyWithPrefix($key);

        /** @var \Redis $redis */
        $redis = $this->redisPool->pick($this->config['connection']);

        try {
            $lua = <<<EOF
local result=true;
local reduceCounterRes=redis.call('decr', KEYS[1]);
if(reduceCounterRes == 0) then
    local delRes=redis.call('del', KEYS[1])
    if(delRes < 0) then
        result=false
    end
end
return result
EOF;
            $result = $redis->eval($lua, [$keyWithPrefix], 1) > 0;
            if ($result) {
                unset($this->shared_locked_keys[$key]);
            }
            return $result;
        } catch (\Throwable $e) {
            throw $e;
        } finally {
            $this->redisPool->release($redis);
        }
    }

    /**
     * @param $key
     * @return mixed
     * @throws \RedisException
     * @throws \Throwable
     */
    protected function lockSharedLock($key)
    {
        $keyWithPrefix = $this->sharedLockKeyWithPrefix($key);

        /** @var \Redis $redis */
        $redis = $this->redisPool->pick($this->config['connection']);
        try {
            $lua = <<<EOF
local result=true;
local existed=redis.call('exists', KEYS[1]);
if(existed >= 1) then
    local counter=redis.call('get', KEYS[1])
    if(counter <= 0) then
        local lockRes=redis.call('set', KEYS[1], -1)
        if(not( lockRes )) then
            result=false
        end
    end
    if(counter > 0) then
        result=false
    end
else
    local initCounterRes=redis.call('set', KEYS[1], -1)
    if(not( initCounterRes )) then
        result=false
    end
end
return result
EOF;
            return $redis->eval($lua, [$keyWithPrefix], 1);
        } catch (\Throwable $e) {
            throw $e;
        } finally {
            $this->redisPool->release($redis);
        }
    }

    /**
     * @param $key
     * @return bool
     * @throws \RedisException
     * @throws \Throwable
     */
    protected function unlockSharedLock($key)
    {
        $keyWithPrefix = $this->sharedLockKeyWithPrefix($key);

        /** @var \Redis $redis */
        $redis = $this->redisPool->pick($this->config['connection']);
        try {
            return $redis->del($keyWithPrefix) >= 0;
        } catch (\Throwable $e) {
            throw $e;
        } finally {
            $this->redisPool->release($redis);
        }
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
        if (!$this->lockSharedLock($key)) {
            return false;
        }

        $keyWithPrefix = $this->lockKeyWithPrefix($key);

        $deferTimerId = null;
        /** @var \Redis $redis */
        $redis = $this->redisPool->pick($this->config['connection']);
        try {
            //因为redis整数对象有缓存，此处value使用1
            if ($ttl > 0) {
                $result = $redis->set($keyWithPrefix, 1, ['NX', 'EX' => $ttl]);
            } else {
                $result = $redis->setnx($keyWithPrefix, 1);
            }
            if ($result) {
                $this->addLockedKey($key, $guard);

                if (is_callable($callback)) {
                    //Defer
                    if ($ttl >= 2) {
                        $deferTimerId = swoole_timer_tick(1000, function () use ($keyWithPrefix, $ttl) {
                            /** @var \Redis $redis */
                            $redis = $this->redisPool->pick($this->config['connection']);
                            try {
                                $lua = <<<EOF
local existed=redis.call('exists', KEYS[1]);
if(existed >= 1) then
    local remainTtl=redis.call('ttl', KEYS[1])
    if(remainTtl <= 1) then
        redis.call('expire', KEYS[1], ARGV[1])
    end
end
return true
EOF;
                                if ($redis->eval($lua, [$keyWithPrefix, $ttl], 1) === false) {
                                    throw new \Exception('Redis eval error:' . $redis->getLastError());
                                }
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

        if (!$this->unlockSharedLock($key)) {
            return false;
        }

        $keyWithPrefix = $this->lockKeyWithPrefix($key);

        /** @var \Redis $redis */
        $redis = $this->redisPool->pick($this->config['connection']);
        try {
            $result = $redis->del($keyWithPrefix);
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
        $keyWithPrefix = $this->lockKeyWithPrefix($key);

        /** @var \Redis $redis */
        $redis = $this->redisPool->pick($this->config['connection']);
        try {
            return $redis->expire($keyWithPrefix, $ttl);
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

    private function addSharedLockedKey($key, $guard = false)
    {
        $this->shared_locked_keys[$key] = [
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
        foreach ($this->locked_keys as $lockedKey) {
            if (!$lockedKey['guard']) {
                $this->unlock($lockedKey['key']);
            }
        }

        foreach ($this->shared_locked_keys as $sharedLockedKey) {
            if (!$sharedLockedKey['guard']) {
                $this->sharedUnLock($sharedLockedKey['key']);
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
