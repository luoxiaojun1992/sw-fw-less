<?php

namespace SwFwLess\components\virtualization;

use SwFwLess\components\provider\WorkerProviderContract;
use SwFwLess\components\virtualization\resource\CGroup;

class Provider implements WorkerProviderContract
{
    public static function bootWorker()
    {
        if (CGroup::support()) {
            //
        }
    }

    public static function shutdownWorker()
    {
        //
    }
}
