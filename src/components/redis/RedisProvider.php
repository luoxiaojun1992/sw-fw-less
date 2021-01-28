<?php

namespace SwFwLess\components\redis;

use SwFwLess\components\provider\RequestProviderContract;
use SwFwLess\components\provider\WorkerProviderContract;

class RedisProvider implements WorkerProviderContract, RequestProviderContract
{
    public static function bootWorker()
    {
        RedisPool::create(\SwFwLess\components\functions\config('redis'));
    }

    public static function shutdownWorker()
    {
        //
    }

    public static function bootRequest()
    {
        RedLock::create(RedisPool::create(), \SwFwLess\components\functions\config('red_lock'));
    }

    public static function shutdownResponse()
    {
        //
    }
}
