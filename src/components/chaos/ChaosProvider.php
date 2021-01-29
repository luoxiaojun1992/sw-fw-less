<?php

namespace SwFwLess\components\chaos;

use SwFwLess\components\provider\AppProviderContract;

class ChaosProvider implements AppProviderContract
{
    public static function bootApp()
    {
        FaultStore::init();
    }

    public static function shutdownApp()
    {
        //
    }
}
