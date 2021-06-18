<?php

namespace SwFwLess\components\chaos;

use SwFwLess\components\provider\AppProviderContract;

class ChaosProvider implements AppProviderContract
{
    public static function bootApp()
    {
        $chaosSwitch = \SwFwLess\components\functions\config('chaos.switch', false);
        if ($chaosSwitch) {
            FaultStore::init();
        }
    }

    public static function shutdownApp()
    {
        //
    }
}
