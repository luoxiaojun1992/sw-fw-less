<?php

namespace SwFwLess\components\swoole\counter;

use SwFwLess\components\di\Container;
use SwFwLess\components\provider\AppProviderContract;
use SwFwLess\components\provider\WorkerProviderContract;

class CounterProvider implements AppProviderContract, WorkerProviderContract
{
    public static function bootApp()
    {
        if (Container::diSwitch()) {
            Counter::init();
        }
    }

    public static function shutdownApp()
    {
        //
    }

    public static function bootWorker()
    {
        if (Container::diSwitch()) {
            Counter::reload();
        }
    }

    public static function shutdownWorker()
    {
        //
    }
}
