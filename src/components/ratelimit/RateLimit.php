<?php

namespace SwFwLess\components\ratelimit;

use SwFwLess\components\redis\RedisPool;

/**
 * Class RateLimit
 *
 * {@inheritdoc}
 *
 * @package SwFwLess\components\ratelimit
 */
class RateLimit
{
    private static $instance;

    /**
     * @var RedisPool
     */
    private $redisPool;

    private $config = ['connection' => 'rate_limit'];

    /**
     * @param RedisPool|null $redisPool
     * @param array $config
     * @return RateLimit
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
     * RateLimit constructor.
     * @param RedisPool $redisPool
     * @param array $config
     */
    public function __construct(RedisPool $redisPool, $config = [])
    {
        $this->redisPool = $redisPool;
        $this->config = array_merge($this->config, $config);
    }

    /**
     * @param $metric
     * @param $period
     * @param $throttle
     * @param $remaining
     * @return bool
     * @throws \Throwable
     */
    public function pass($metric, $period, $throttle, &$remaining = null)
    {
        /** @var \Redis $redis */
        $redis = $this->redisPool->pick($this->config['connection']);
        try {
            if (intval($redis->get($metric)) >= $throttle) {
                return false;
            }

            if ($period > 0) {
                $lua = <<<EOF
local new_value=redis.call('incr', KEYS[1]);
local ttl_value=redis.call('ttl', KEYS[1]);
if(ttl_value == -1) then 
redis.call('expire', KEYS[1], ARGV[1]) 
end
return new_value
EOF;
                $passed = intval($redis->eval($lua, [$metric, $period], 1));
            } else {
                $lua = <<<EOF
local new_value=redis.call('incr', KEYS[1]);
local ttl_value=redis.call('ttl', KEYS[1]);
if(ttl_value > -1) then 
redis.call('persist', KEYS[1]) 
end
return new_value
EOF;
                $passed = intval($redis->eval($lua, [$metric], 1));
            }

            $remaining = $throttle - $passed;
            return ($passed > 0) && ($passed <= $throttle);
        } catch (\Throwable $e) {
            throw $e;
        } finally {
            $this->redisPool->release($redis);
        }
    }

    /**
     * @param $metric
     * @param $period
     * @param $throttle
     * @return int
     * @throws \RedisException
     * @throws \Throwable
     */
    public function giveBack($metric, $period, $throttle)
    {
        /** @var \Redis $redis */
        $redis = $this->redisPool->pick($this->config['connection']);
        try {
            if ($period > 0) {
                $lua = <<<EOF
local is_existed=redis.call('exists', KEYS[1]);
if(is_existed == 0) then
return 0
end
local new_value=redis.call('decr', KEYS[1]);
local ttl_value=redis.call('ttl', KEYS[1]);
if(ttl_value == -1) then 
redis.call('expire', KEYS[1], ARGV[1]) 
end
return new_value
EOF;
                $passed = intval($redis->eval($lua, [$metric, $period], 1));
            } else {
                $lua = <<<EOF
local is_existed=redis.call('exists', KEYS[1]);
if(is_existed == 0) then
return 0
end
local new_value=redis.call('decr', KEYS[1]);
local ttl_value=redis.call('ttl', KEYS[1]);
if(ttl_value > -1) then 
redis.call('persist', KEYS[1]) 
end
return new_value
EOF;
                $passed = intval($redis->eval($lua, [$metric], 1));
            }

            return $throttle - $passed;
        } catch (\Throwable $e) {
            throw $e;
        } finally {
            $this->redisPool->release($redis);
        }
    }

    /**
     * @param $metric
     * @return int
     * @throws \RedisException
     * @throws \Throwable
     */
    public function clear($metric)
    {
        /** @var \Redis $redis */
        $redis = $this->redisPool->pick($this->config['connection']);
        try {
            return $redis->del($metric);
        } catch (\Throwable $e) {
            throw $e;
        } finally {
            $this->redisPool->release($redis);
        }
    }
}
