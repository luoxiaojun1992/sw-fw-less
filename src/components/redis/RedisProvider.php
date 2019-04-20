<?php

namespace SwFwLess\components\redis;

use SwFwLess\components\provider\AbstractProvider;
use SwFwLess\components\provider\RequestProvider;

class RedisProvider extends AbstractProvider implements RequestProvider
{
    public static function bootRequest()
    {
        parent::bootRequest();

        if (config('redis.switch')) {
            RedisPool::create(config('redis'));
        }
    }
}
