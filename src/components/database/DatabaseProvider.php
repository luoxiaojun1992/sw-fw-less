<?php

namespace SwFwLess\components\database;

use SwFwLess\components\provider\WorkerProviderContract;

class DatabaseProvider implements WorkerProviderContract
{
    public static function bootWorker()
    {
        Database::init();
    }

    public static function shutdownWorker()
    {
        //
    }
}
