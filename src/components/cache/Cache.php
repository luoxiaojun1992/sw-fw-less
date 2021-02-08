<?php

namespace SwFwLess\components\cache;

use SwFwLess\components\redis\RedisPool;
use SwFwLess\facades\RedLock;

/**
 * Class Cache
 *
 * @package SwFwLess\components\cache
 */
class Cache
{
    private static $instance;

    /**
     * @var RedisPool
     */
    private $redisPool;

    private $config = [
        'connection' => 'cache',
        'update_lock_ttl' => 10,
    ];

    /**
     * @param RedisPool|null $redisPool
     * @param array $config
     * @return self
     */
    public static function create(RedisPool $redisPool = null, $config = [])
    {
        if (self::$instance instanceof self) {
            return self::$instance;
        }

        if (!is_null($redisPool)) {
            return self::$instance = new self($redisPool, $config);
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
    }

    /**
     * @param $key
     * @param $value
     * @param int $ttl
     * @return bool|mixed
     * @throws \Throwable
     */
    public function set($key, $value, $ttl = 0)
    {
        /** @var \Redis $redis */
        $redis = $this->redisPool->pick($this->config['connection']);

        try {
            if ($ttl > 0) {
                $lua = <<<EOF
local resSet = redis.call('set', KEYS[1], ARGV[1]);
if(resSet) then
local resExp = redis.call('set', KEYS[2], ARGV[2]);
if(resExp) then
redis.call('expire', KEYS[2], ARGV[2])
end
end
return resSet
EOF;
                //todo prefix config
                return $redis->eval($lua, ['cache:' . $key, 'ttl:' . $key, $value, $ttl], 2);
            } else {
                $lua = <<<EOF
local resSet = redis.call('set', KEYS[1], ARGV[1]);
if(resSet) then
local resExp = redis.call('set', KEYS[2], ARGV[2]);
end
return resSet
EOF;
                return $redis->eval($lua, ['cache:' . $key, 'ttl:' . $key, $value, 1], 2);
            }
        } catch (\Throwable $e) {
            throw $e;
        } finally {
            $this->redisPool->release($redis);
        }

        return false;
    }

    /**
     * @param $key
     * @return bool|string
     * @throws \Throwable
     */
    public function get($key)
    {
        /** @var \Redis $redis */
        $redis = $this->redisPool->pick($this->config['connection']);

        try {
            //todo perf-optimize: get ttl flag and data using pipeline, reduce io times
            if ($redis->get('ttl:' . $key) === false) {
                if (RedLock::lock('update:cache:' . $key, $this->config['update_lock_ttl'])) {
                    if ($redis->get($key . ':ttl') === false) {
                        return false;
                    }
                }
            }

            return $redis->get('cache:' . $key);
        } catch (\Throwable $e) {
            throw $e;
        } finally {
            $this->redisPool->release($redis);
        }
    }
}
