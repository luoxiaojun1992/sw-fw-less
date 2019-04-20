<?php

namespace SwFwLess\components\chaos;

use SwFwLess\components\provider\AbstractProvider;
use SwFwLess\components\provider\AppProvider;

class ChaosProvider extends AbstractProvider implements AppProvider
{
    public static function bootApp()
    {
        parent::bootApp();

        FaultStore::init();
    }
}
