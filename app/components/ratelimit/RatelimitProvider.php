<?php

namespace App\components\ratelimit;

use App\components\provider\AbstractProvider;
use App\components\provider\RequestProvider;

class RatelimitProvider extends AbstractProvider implements RequestProvider
{
    public static function bootRequest()
    {
        parent::bootRequest();

        if (config('redis.switch')) {
            //Rate limiter
            RateLimit::create(
                \App\components\redis\RedisPool::create(),
                config('rate_limit')
            );
        }
    }
}
