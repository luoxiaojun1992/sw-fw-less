<?php

namespace SwFwLess\components\cache;

use SwFwLess\components\provider\WorkerProviderContract;

class CacheProvider implements WorkerProviderContract
{
    public static function bootWorker()
    {
        Cache::create(
            \SwFwLess\components\redis\RedisPool::create(),
            \SwFwLess\components\functions\config('cache')
        );
    }

    public static function shutdownWorker()
    {
        //
    }
}
