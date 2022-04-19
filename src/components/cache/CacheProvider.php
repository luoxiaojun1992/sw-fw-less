<?php

namespace SwFwLess\components\cache;

use SwFwLess\components\provider\WorkerProviderContract;
use SwFwLess\components\redis\RedisPool;

class CacheProvider implements WorkerProviderContract
{
    public static function bootWorker()
    {
        if (RedisPool::enable()) {
            Cache::create(
                \SwFwLess\components\redis\RedisPool::create(),
                \SwFwLess\components\functions\config('cache')
            );
        }
    }

    public static function shutdownWorker()
    {
        //
    }
}
