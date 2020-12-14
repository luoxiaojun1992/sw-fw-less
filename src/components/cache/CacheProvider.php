<?php

namespace SwFwLess\components\cache;

use SwFwLess\components\provider\AbstractProvider;

class CacheProvider extends AbstractProvider
{
    public static function bootWorker()
    {
        parent::bootWorker();

        Cache::create(
            \SwFwLess\components\redis\RedisPool::create(),
            \SwFwLess\components\functions\config('cache')
        );
    }
}
