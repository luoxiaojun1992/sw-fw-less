<?php

namespace App\components\chaos;

use App\components\provider\AbstractProvider;
use App\components\provider\AppProvider;

class ChaosProvider extends AbstractProvider implements AppProvider
{
    public static function bootApp()
    {
        parent::bootApp();

        FaultStore::init();
    }
}
