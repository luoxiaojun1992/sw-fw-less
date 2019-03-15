<?php

namespace App\components\storage;

use App\components\core\AbstractProvider;
use App\components\core\RequestProvider;

class StorageProvider extends AbstractProvider implements RequestProvider
{
    public static function bootRequest()
    {
        parent::bootRequest();

        if (config('storage.switch')) {
            Storage::init();
        }
    }
}
