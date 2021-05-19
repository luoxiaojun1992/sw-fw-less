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

    private $config = [
        'connection' => 'rate_limit',
        'metric_prefix' => 'rate_limit_metric:',
    ];

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

    protected function metricPrefix()
    {
        return $this->config['metric_prefix'];
    }

    protected function metricWithPrefix($metric)
    {
        return $this->metricPrefix() . $metric;
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
            $metricWithPrefix = $this->metricWithPrefix($metric);

            if (intval($redis->get($metricWithPrefix)) >= $throttle) {
                return false;
            }

            if ($period > 0) {
                $lua = <<<EOF
local new_value=redis.call('incr', KEYS[1]);
local ttl_value=redis.call('ttl', KEYS[1]);
if(ttl_value == -1) then 
    local expire_res=redis.call('expire', KEYS[1], ARGV[1])
    if(expire_res == 0) then
        new_value=0
    end
end
return new_value
EOF;
                $passed = $redis->eval($lua, [$metricWithPrefix, $period], 1);

                if ($passed === false) {
                    throw new \Exception('Redis eval error:' . $redis->getLastError());
                }

                $passed = intval($passed);
            } else {
                $lua = <<<EOF
local new_value=redis.call('incr', KEYS[1]);
local ttl_value=redis.call('ttl', KEYS[1]);
if(ttl_value > -1) then 
    redis.call('persist', KEYS[1]) 
end
return new_value
EOF;
                $passed = $redis->eval($lua, [$metricWithPrefix], 1);

                if ($passed === false) {
                    throw new \Exception('Redis eval error:' . $redis->getLastError());
                }

                $passed = intval($passed);
            }

            $remaining = $throttle - $passed;
            return ($passed > 0) && ($passed <= $throttle);
        } catch (\Throwable $e) {
            throw $e;
        } finally {
            $this->redisPool->release($redis);
        }
    }

    public function supportGivingBack()
    {
        return true;
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
            $metricWithPrefix = $this->metricWithPrefix($metric);

            if ($period > 0) {
                $lua = <<<EOF
local continue=true;
local new_value=0;
local is_existed=redis.call('exists', KEYS[1]);
if(is_existed == 0) then
    continue=false
end
if(continue) then
    new_value=redis.call('decr', KEYS[1])
    local ttl_value=redis.call('ttl', KEYS[1])
    if(ttl_value == -1) then 
        local expire_res=redis.call('expire', KEYS[1], ARGV[1])
        if(expire_res == 0) then
            new_value=0
        end
    end
end
return new_value
EOF;
                $passed = $redis->eval($lua, [$metricWithPrefix, $period], 1);

                if ($passed === false) {
                    throw new \Exception('Redis eval error:' . $redis->getLastError());
                }

                $passed = intval($passed);
            } else {
                $lua = <<<EOF
local continue=true;
local new_value=0;
local is_existed=redis.call('exists', KEYS[1]);
if(is_existed == 0) then
    continue=false
end
if(continue) then
    new_value=redis.call('decr', KEYS[1])
    local ttl_value=redis.call('ttl', KEYS[1])
    if(ttl_value > -1) then 
        redis.call('persist', KEYS[1]) 
    end
end
return new_value
EOF;
                $passed = $redis->eval($lua, [$metricWithPrefix], 1);

                if ($passed === false) {
                    throw new \Exception('Redis eval error:' . $redis->getLastError());
                }

                $passed = intval($passed);
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
            return $redis->del($this->metricWithPrefix($metric));
        } catch (\Throwable $e) {
            throw $e;
        } finally {
            $this->redisPool->release($redis);
        }
    }
}
