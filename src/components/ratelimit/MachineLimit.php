<?php

namespace SwFwLess\components\ratelimit;

use SwFwLess\components\traits\Singleton;

class MachineLimit
{
    //TODO

    use Singleton;

    /**
     * MemLimit constructor.
     * @param array $config
     */
    public function __construct($config = [])
    {
        $this->config = array_merge($this->config, $config);
    }

    public function pass($metric, $period, $throttle, &$remaining = null)
    {
        if (!RateLimitFactory::resolve(RateLimitFactory::ALGORITHM_MEMORY_USAGE)->pass(
            $metric, $period, $throttle, $remaining
        )) {
            return false;
        }

        if (!RateLimitFactory::resolve(RateLimitFactory::ALGORITHM_SYS_LOAD)->pass(
            $metric, $period, $throttle, $remaining
        )) {
            return false;
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
