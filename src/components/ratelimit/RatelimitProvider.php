<?php

namespace SwFwLess\components\ratelimit;

use SwFwLess\components\provider\WorkerProviderContract;

class RatelimitProvider implements WorkerProviderContract
{
    public static function bootWorker()
    {
        //Rate limiter
        RateLimit::create(
            \SwFwLess\components\redis\RedisPool::create(),
            \SwFwLess\components\functions\config('rate_limit')
        );
    }

    public static function shutdownWorker()
    {
        //
    }
}
