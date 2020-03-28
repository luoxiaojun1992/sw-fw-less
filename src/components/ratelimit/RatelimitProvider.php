<?php

namespace SwFwLess\components\ratelimit;

use SwFwLess\components\provider\AbstractProvider;

class RatelimitProvider extends AbstractProvider
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
