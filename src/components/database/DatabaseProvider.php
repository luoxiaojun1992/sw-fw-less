<?php

namespace SwFwLess\components\database;

use SwFwLess\components\provider\WorkerProviderContract;

class DatabaseProvider implements WorkerProviderContract
{
    public static function bootWorker()
    {
        ConnectionPool::create(Database::config());
    }

    public static function shutdownWorker()
    {
        //
    }
}
