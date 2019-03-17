<?php

namespace App\components\ratelimit;

use App\components\core\AbstractProvider;
use App\components\core\RequestProvider;

class RatelimitProvider extends AbstractProvider implements RequestProvider
{
    public static function bootRequest()
    {
        parent::bootRequest();

        if (config('redis.switch')) {
            //Rate limiter
            RateLimit::create(
                \App\components\RedisPool::create(),
                config('rate_limit')
            );
        }
    }
}
