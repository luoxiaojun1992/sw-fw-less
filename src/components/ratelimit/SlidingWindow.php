<?php

namespace SwFwLess\components\ratelimit;

use SwFwLess\components\redis\RedisPool;

class SlidingWindow
{
    private static $instance;

    /**
     * @var RedisPool
     */
    private $redisPool;

    private $config = [
        'connection' => 'sliding_window',
        'metric_prefix' => 'sliding_window_metric:',
        'metric_period_prefix' => 'sliding_window_metric_period:',
        'metric_window_num_prefix' => 'sliding_window_metric_window_num:',
    ];

    /**
     * @param RedisPool|null $redisPool
     * @param array $config
     * @return static
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

    protected function metricPeriodPrefix()
    {
        return $this->config['metric_period_prefix'];
    }

    protected function metricWithPeriodPrefix($metric)
    {
        return $this->metricPeriodPrefix() . $metric;
    }

    protected function metricWindowNumPrefix()
    {
        return $this->config['metric_window_num_prefix'];
    }

    protected function metricWithWindowPrefix($metric)
    {
        return $this->metricWindowNumPrefix() . $metric;
    }

    /**
     * @param $metric
     * @param $period
     * @param $throttle
     * @param $remaining
     * @param $windowTotal
     * @return bool
     * @throws \Throwable
     */
    public function pass($metric, $period, $throttle, $windowTotal = 10, &$remaining = null)
    {
        /** @var \Redis $redis */
        $redis = $this->redisPool->pick($this->config['connection']);
        try {
            $metricWithPrefix = $this->metricWithPrefix($metric);

            if (array_sum($redis->hVals($metricWithPrefix)) >= $throttle) {
                return false;
            }

            $lua = <<<EOF
local continue=true;
local window_num=1;
local max_window_num=tonumber(ARGV[2]);
local period=tonumber(ARGV[1]);
if(redis.call('exists', KEYS[3]) == 1) then
    window_num=tonumber(redis.call('get', KEYS[3]))
end

local period_flag=redis.call('exists', KEYS[2]);
if(period_flag == 0) then
    local set_period_res=redis.call('set', KEYS[2], '1', 'EX', period)
    if( not(set_period_res) ) then
        continue=false
    end
    if( continue ) then
        window_num=redis.call('incr', KEYS[3])
        if(window_num > max_window_num) then
            window_num=1
            local reset_window_num_res=redis.call('set', KEYS[3], '1')
            if( not(reset_window_num_res) ) then
                continue=false
            end
        end
    end
    if( continue ) then
        local window_existed=redis.call('hexists', KEYS[1], 'window_' .. window_num)
        local reset_window_res=redis.call('hset', KEYS[1], 'window_' .. window_num, '0')
        if(window_existed == 1) then
            if(reset_window_res ~= 0) then
                continue=false
            end
        end
        if(window_existed == 0) then
            if(reset_window_res ~= 1) then
                continue=false
            end
        end
    end
end
if(period_flag == 1) then
    local period_ttl=redis.call('ttl', KEYS[2])
    if(period_ttl == -1) then
        local expire_res=redis.call('expire', KEYS[2], period)
        if(expire_res == 0) then
            continue=false
        end
    end
end

local total_passed=0;

if( continue ) then
    local passed=redis.call('hincrby', KEYS[1], 'window_' .. window_num, 1);
    if(passed == 0) then
        continue=false
    end
end

if(continue) then
    local all_windows=redis.call('hvals', KEYS[1])
    if(#all_windows < 1) then
        continue=false
    end
    if(continue) then
        for i=1, #all_windows do
            total_passed=total_passed + tonumber(all_windows[i])
        end
    end
end

return total_passed
EOF;

            $passed = $redis->eval(
                $lua,
                [
                    $metricWithPrefix,
                    $this->metricWithPeriodPrefix($metric),
                    $this->metricWithWindowPrefix($metric),
                    $period,
                    $windowTotal
                ],
                3
            );
            if ($passed === false) {
                throw new \Exception('Redis eval error:' . $redis->getLastError());
            }

            $passed = intval($passed);

            $remaining = $throttle - $passed;
            return ($passed > 0) && ($passed <= $throttle);
        } catch (\Throwable $e) {
            throw $e;
        } finally {
            $this->redisPool->release($redis);
        }
    }

    public function giveBack($metric, $period, $throttle)
    {
        //todo
        return 0;
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
            return $redis->del(
                $this->metricWithPrefix($metric),
                $this->metricWithPeriodPrefix($metric),
                $this->metricWithWindowPrefix($metric)
            );
        } catch (\Throwable $e) {
            throw $e;
        } finally {
            $this->redisPool->release($redis);
        }
    }
}
