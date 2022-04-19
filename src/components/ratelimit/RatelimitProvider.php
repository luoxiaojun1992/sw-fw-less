<?php

namespace SwFwLess\components\ratelimit;

use SwFwLess\components\provider\WorkerProviderContract;
use SwFwLess\components\redis\RedisPool;

class RatelimitProvider implements WorkerProviderContract
{
    public static function bootWorker()
    {
        RateLimit::create(
            \SwFwLess\components\redis\RedisPool::create(),
            \SwFwLess\components\functions\config(
                'rate_limit.drivers.' . RateLimitFactory::ALGORITHM_LEAKY_BUCKET
            )
        );

        SlidingWindow::create(
            RedisPool::create(),
            \SwFwLess\components\functions\config(
                'rate_limit.drivers.' . RateLimitFactory::ALGORITHM_SLIDING_WINDOW
            )
        );

        MemLimit::create(
            \SwFwLess\components\functions\config(
                'rate_limit.drivers.' . RateLimitFactory::ALGORITHM_MEMORY_USAGE, []
            )
        );

        SysLoadLimit::create(
            \SwFwLess\components\functions\config(
                'rate_limit.drivers.' . RateLimitFactory::ALGORITHM_SYS_LOAD, []
            )
        );

        MachineLimit::create(
            \SwFwLess\components\functions\config(
                'rate_limit.drivers.' . RateLimitFactory::ALGORITHM_MACHINE, []
            )
        );
    }

    public static function shutdownWorker()
    {
        //
    }
}
