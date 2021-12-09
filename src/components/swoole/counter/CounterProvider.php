<?php

namespace SwFwLess\components\swoole\counter;

use SwFwLess\components\Config;
use SwFwLess\components\di\Container;
use SwFwLess\components\provider\AppProviderContract;
use SwFwLess\components\provider\WorkerProviderContract;

class CounterProvider implements AppProviderContract, WorkerProviderContract
{
    public static function config()
    {
        return Config::get('counter', []);
    }

    public static function bootApp()
    {
        if (Container::diSwitch()) {
            Counter::init(static::config());
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
