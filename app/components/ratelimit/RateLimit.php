<?php

namespace App\components\ratelimit;

use App\components\redis\RedisPool;

/**
 * Class RateLimit
 *
 * {@inheritdoc}
 *
 * @package App\components\ratelimit
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
            if ($redis->get($metric) >= $throttle) {
                return false;
            }

            $lua = <<<EOF
local new_value=redis.call('incr', KEYS[1]);
if(new_value == 1) then 
redis.call('expire', KEYS[1], ARGV[1]) 
end
return new_value
EOF;
            $passed = $redis->eval($lua, [$metric, $period], 1);
            $remaining = $throttle - $passed;
            return $passed <= $throttle;
        } catch (\Throwable $e) {
            throw $e;
        } finally {
            $this->redisPool->release($redis);
        }
    }

    /**
     * @param $metric
     * @throws \Throwable
     */
    public function clear($metric)
    {
        /** @var \Redis $redis */
        $redis = $this->redisPool->pick($this->config['connection']);
        try {
            $redis->del($metric);
        } catch (\Throwable $e) {
            throw $e;
        } finally {
            $this->redisPool->release($redis);
        }
    }
}
