<?php

namespace SwFwLess\components\database;

use SwFwLess\components\provider\WorkerProviderContract;

class DatabaseProvider implements WorkerProviderContract
{
    public static function bootWorker()
    {
        ConnectionPool::create(\SwFwLess\components\functions\config('database'));
    }

    public static function shutdownWorker()
    {
        //
    }
}
