<?php

namespace SwFwLess\components\storage;

use SwFwLess\components\provider\AbstractProvider;
use SwFwLess\components\provider\RequestProvider;

class StorageProvider extends AbstractProvider implements RequestProvider
{
    public static function bootRequest()
    {
        parent::bootRequest();

        Storage::init();
    }
}
