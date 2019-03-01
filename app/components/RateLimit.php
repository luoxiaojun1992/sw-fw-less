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
        $key = $this->redisPool->getKey($metric);
        /** @var \Redis $redis */
        $redis = $this->redisPool->pick();
        try {
            if ($redis->get($key) >= $throttle) {
                return false;
            }

            //todo use lua script
            $passed = $redis->incr($key);
            if ($passed == 1) {
                $redis->expire($key, $period);
            }
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
            $redis->del($this->redisPool->getKey($metric));
        } catch (\Exception $e) {
            throw $e;
        } finally {
            $this->redisPool->release($redis);
        }
    }
}
