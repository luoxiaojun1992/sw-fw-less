<?php

namespace SwFwLess\components\mysql;

use SwFwLess\components\provider\WorkerProviderContract;

class MysqlProvider implements WorkerProviderContract
{
    public static function bootWorker()
    {
        MysqlPool::create(\SwFwLess\components\functions\config('mysql'));
    }

    public static function shutdownWorker()
    {
        //
    }
}
