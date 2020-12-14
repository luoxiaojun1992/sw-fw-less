<?php

namespace SwFwLess\components\redis;

use SwFwLess\components\provider\AbstractProvider;

class RedisProvider extends AbstractProvider
{
    public static function bootWorker()
    {
        parent::bootWorker();

        RedisPool::create(\SwFwLess\components\functions\config('redis'));
    }

    public static function bootRequest()
    {
        parent::bootRequest();

        RedLock::create(RedisPool::create(), \SwFwLess\components\functions\config('red_lock'));
    }
}
