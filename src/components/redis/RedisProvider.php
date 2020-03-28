<?php

namespace SwFwLess\components\redis;

use SwFwLess\components\provider\AbstractProvider;

class RedisProvider extends AbstractProvider
{
    public static function bootWorker()
    {
        parent::bootWorker();

        RedisPool::create(config('redis'));
    }

    public static function bootRequest()
    {
        parent::bootRequest();

        RedLock::create(RedisPool::create(), config('red_lock'));
    }
}
