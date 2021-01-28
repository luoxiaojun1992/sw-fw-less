<?php

namespace SwFwLess\components\di;

use SwFwLess\components\provider\WorkerProviderContract;

/**
 * Class ContainerProvider
 * @package SwFwLess\components\di
 */
class ContainerProvider implements WorkerProviderContract
{
    /**
     * @throws \Exception
     */
    public static function bootWorker()
    {
        if (Container::diSwitch()) {
            Container::create();
        }
    }

    public static function shutdownWorker()
    {
        //
    }
}
