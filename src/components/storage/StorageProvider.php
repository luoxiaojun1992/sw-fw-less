<?php

namespace SwFwLess\components\storage;

use SwFwLess\components\provider\AbstractProvider;

class StorageProvider extends AbstractProvider
{
    public static function bootWorker()
    {
        parent::bootWorker();

        Storage::init();
    }
}
