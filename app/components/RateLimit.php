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
            if ($redis->get($key) < $throttle) {
                $redis->multi(\Redis::PIPELINE);
                $redis->incr($key);
                $redis->expire($key, $period);
                $result = $redis->exec();
                return $result[0] <= $throttle;
            } else {
                return false;
            }
        } catch (\Exception $e) {
            throw $e;
        } finally {
            $this->redisPool->release($redis);
        }

        return false;
    }
}
