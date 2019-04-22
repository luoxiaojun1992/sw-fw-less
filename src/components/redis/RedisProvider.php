<?php

namespace SwFwLess\components\redis;

use SwFwLess\components\provider\AbstractProvider;
use SwFwLess\components\provider\RequestProvider;

class RedisProvider extends AbstractProvider implements RequestProvider
{
    public static function bootRequest()
    {
        parent::bootRequest();

        RedisPool::create(config('redis'));
    }
}
