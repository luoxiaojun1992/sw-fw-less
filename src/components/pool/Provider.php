<?php

namespace SwFwLess\components\pool;

use SwFwLess\components\provider\WorkerProviderContract;

class Provider implements WorkerProviderContract
{
    public static function bootWorker()
    {
        ObjectPool::create(\SwFwLess\components\functions\config('pool'));
    }

    public static function shutdownWorker()
    {
        //
    }
}
