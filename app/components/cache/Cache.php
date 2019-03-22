<?php

namespace App\components\cache;

use App\components\RedisPool;
use App\facades\RedLock;

/**
 * Class Cache
 *
 * @package App\components\cache
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

        return self::$instance = new self($redisPool, $config);
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
     * @throws \RedisException
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
                return $redis->eval($lua, [$key, $key . ':ttl', $value, $ttl], 2);
            }

            return $redis->set($key, $value);
        } catch (\Exception $e) {
            throw $e;
        } finally {
            $this->redisPool->release($redis);
        }
    }

    /**
     * @param $key
     * @return bool|string
     * @throws \RedisException
     */
    public function get($key)
    {
        /** @var \Redis $redis */
        $redis = $this->redisPool->pick($this->config['connection']);

        try {
            if ($redis->get($key . ':ttl') === false) {
                //todo config ttl
                if (RedLock::lock('lock:update:cache:' . $key, $this->config['update_lock_ttl'])) {
                    if ($redis->get($key . ':ttl') === false) {
                        return false;
                    }
                }
            }

            return $redis->get($key);
        } catch (\Exception $e) {
            throw $e;
        } finally {
            $this->redisPool->release($redis);
        }
    }
}
