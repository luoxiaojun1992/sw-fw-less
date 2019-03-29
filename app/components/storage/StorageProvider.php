<?php

namespace App\components\storage;

use App\components\provider\AbstractProvider;
use App\components\provider\RequestProvider;

class StorageProvider extends AbstractProvider implements RequestProvider
{
    public static function bootRequest()
    {
        parent::bootRequest();

        Storage::init();
    }
}
