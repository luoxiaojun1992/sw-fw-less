<?php

namespace SwFwLess\components\storage;

use SwFwLess\components\provider\CommandProviderContract;
use SwFwLess\components\provider\WorkerProviderContract;

class StorageProvider implements WorkerProviderContract, CommandProviderContract
{
    public static function bootWorker()
    {
        Storage::init();
    }

    public static function shutdownWorker()
    {
        //
    }

    public static function bootCommand()
    {
        Storage::init();
    }

    public static function shutdownCommand()
    {
        //
    }
}
