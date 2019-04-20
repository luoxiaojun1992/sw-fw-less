<?php

namespace SwFwLess\components\ratelimit;

use SwFwLess\components\provider\AbstractProvider;
use SwFwLess\components\provider\RequestProvider;

class RatelimitProvider extends AbstractProvider implements RequestProvider
{
    public static function bootRequest()
    {
        parent::bootRequest();

        if (config('redis.switch')) {
            //Rate limiter
            RateLimit::create(
                \SwFwLess\components\redis\RedisPool::create(),
                config('rate_limit')
            );
        }
    }
}
