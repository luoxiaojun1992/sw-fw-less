<?php

namespace SwFwLess\components\ratelimit;

use SwFwLess\components\provider\WorkerProviderContract;
use SwFwLess\components\redis\RedisPool;

class RatelimitProvider implements WorkerProviderContract
{
    public static function bootWorker()
    {
        //Rate limiter
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
                'rate_limit.drivers.' . RateLimitFactory::ALGORITHM_MEMORY_USAGE
            )
        );
    }

    public static function shutdownWorker()
    {
        //
    }
}
