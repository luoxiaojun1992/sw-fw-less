<?php

namespace SwFwLess\components\cache;

use SwFwLess\components\provider\AbstractProvider;
use SwFwLess\components\provider\WorkerProvider;

class CacheProvider extends AbstractProvider implements WorkerProvider
{
    public static function bootWorker()
    {
        parent::bootWorker();

        Cache::create(\SwFwLess\components\redis\RedisPool::create(), config('cache'));
    }
}
