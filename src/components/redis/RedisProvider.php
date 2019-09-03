<?php

namespace SwFwLess\components\redis;

use SwFwLess\components\provider\AbstractProvider;
use SwFwLess\components\provider\WorkerProvider;

class RedisProvider extends AbstractProvider implements WorkerProvider
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
