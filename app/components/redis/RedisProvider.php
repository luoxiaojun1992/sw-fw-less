<?php

namespace App\components\redis;

use App\components\core\AbstractProvider;
use App\components\core\RequestProvider;

class RedisProvider extends AbstractProvider implements RequestProvider
{
    public static function bootRequest()
    {
        parent::bootRequest();

        if (config('redis.switch')) {
            \App\components\RedisPool::create(config('redis'));

            //Rate limiter
            \App\components\RateLimit::create(
                \App\components\RedisPool::create(),
                config('rate_limit')
            );
        }
    }
}
