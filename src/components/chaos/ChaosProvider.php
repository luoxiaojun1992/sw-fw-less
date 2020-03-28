<?php

namespace SwFwLess\components\chaos;

use SwFwLess\components\provider\AbstractProvider;

class ChaosProvider extends AbstractProvider
{
    public static function bootApp()
    {
        parent::bootApp();

        FaultStore::init();
    }
}
