<?php

namespace SwFwLess\components\redis;

use SwFwLess\components\provider\RequestProviderContract;
use SwFwLess\components\provider\WorkerProviderContract;

class RedisProvider implements WorkerProviderContract, RequestProviderContract
{
    public static function bootWorker()
    {
        RedisPool::create(RedisPool::config());
    }

    public static function shutdownWorker()
    {
        //
    }

    public static function bootRequest()
    {
        if (RedisPool::enable()) {
            RedLock::create(RedisPool::create(), \SwFwLess\components\functions\config('red_lock'));
        }
    }

    public static function shutdownResponse()
    {
        //
    }
}
