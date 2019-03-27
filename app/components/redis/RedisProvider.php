<?php

namespace App\components\redis;

use App\components\provider\AbstractProvider;
use App\components\provider\RequestProvider;

class RedisProvider extends AbstractProvider implements RequestProvider
{
    public static function bootRequest()
    {
        parent::bootRequest();

        if (config('redis.switch')) {
            \App\components\RedisPool::create(config('redis'));
        }
    }
}
