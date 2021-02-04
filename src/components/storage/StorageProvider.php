<?php

namespace SwFwLess\components\storage;

use SwFwLess\components\provider\WorkerProviderContract;

class StorageProvider implements WorkerProviderContract
{
    public static function bootWorker()
    {
        Storage::init();
    }

    public static function shutdownWorker()
    {
        //
    }
}
