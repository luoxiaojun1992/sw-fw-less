<?php

namespace SwFwLess\components\ratelimit;

use SwFwLess\components\provider\AbstractProvider;
use SwFwLess\components\provider\WorkerProvider;

class RatelimitProvider extends AbstractProvider implements WorkerProvider
{
    public static function bootWorker()
    {
        parent::bootWorker();

        //Rate limiter
        RateLimit::create(
            \SwFwLess\components\redis\RedisPool::create(),
            config('rate_limit')
        );
    }
}
