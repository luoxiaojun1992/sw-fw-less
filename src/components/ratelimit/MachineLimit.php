<?php

namespace SwFwLess\components\ratelimit;

use SwFwLess\components\traits\Singleton;

class MachineLimit implements RateLimitContract
{
    use Singleton;

    /**
     * @var RateLimitContract[]
     */
    protected $subRateLimits = [];

    protected $config = [];

    /**
     * MemLimit constructor.
     * @param array $config
     */
    public function __construct($config = [])
    {
        $this->config = array_merge($this->config, $config);
        $this->registerDefaultRateLimits();
    }

    protected function registerDefaultRateLimits()
    {
        //todo injected from construct method
        return $this->registerRateLimits([
            RateLimitFactory::resolve(RateLimitFactory::ALGORITHM_MEMORY_USAGE),
            RateLimitFactory::resolve(RateLimitFactory::ALGORITHM_SYS_LOAD)
        ]);
    }

    public function registerRateLimits($rateLimits)
    {
        foreach ($rateLimits as $rateLimit) {
            $this->registerRateLimit($rateLimit);
        }
        return $this;
    }

    /**
     * @param $rateLimit
     * @return $this
     */
    public function registerRateLimit($rateLimit)
    {
        $this->subRateLimits[] = $rateLimit;
        return $this;
    }

    public function pass($metric, $period, $throttle, &$remaining = null)
    {
        foreach ($this->subRateLimits as $rateLimit) {
            if (!$rateLimit->pass(
                $metric, $period, $throttle
            )) {
                return false;
            }
        }

        return true;
    }

    public function supportGivingBack()
    {
        return false;
    }

    public function clear($metric)
    {
        return false;
    }
}
