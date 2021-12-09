<?php

namespace SwFwLess\components\swoole\atomic;

use SwFwLess\components\Config;
use SwFwLess\components\provider\AppProviderContract;

class AtomicProvider implements AppProviderContract
{
    public static function bootApp()
    {
        Atomic::init(Config::get('atomic', []));
    }

    public static function shutdownApp()
    {
        //
    }
}
