<?php

namespace App\components;

/**
 * Class RateLimit
 *
 * {@inheritdoc}
 *
 * @package App\components
 */
class RateLimit
{
    private static $instance;

    /**
     * @var RedisPool
     */
    private $redisPool;

    /**
     * @param RedisPool|null $redisPool
     * @return RateLimit
     */
    public static function create(RedisPool $redisPool = null)
    {
        if (self::$instance instanceof self) {
            return self::$instance;
        }

        return self::$instance = new self($redisPool);
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
     * @param $metric
     * @param $period
     * @param $throttle
     * @return bool
     * @throws \Exception
     */
    public function pass($metric, $period, $throttle)
    {
        /** @var \Redis $redis */
        $redis = $this->redisPool->pick();
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
            return $passed <= $throttle;
        } catch (\Exception $e) {
            throw $e;
        } finally {
            $this->redisPool->release($redis);
        }
    }

    /**
     * @param $metric
     * @throws \Exception
     */
    public function clear($metric)
    {
        /** @var \Redis $redis */
        $redis = $this->redisPool->pick();
        try {
            $redis->del($metric);
        } catch (\Exception $e) {
            throw $e;
        } finally {
            $this->redisPool->release($redis);
        }
    }
}
