<?php

namespace SwFwLess\components\swoole\counter;

use SwFwLess\components\di\Container;
use SwFwLess\components\provider\AbstractProvider;

class CounterProvider extends AbstractProvider
{
    public static function bootApp()
    {
        parent::bootApp();

        if (Container::diSwitch()) {
            Counter::init();
        }
    }

    public static function bootWorker()
    {
        parent::bootWorker();

        if (Container::diSwitch()) {
            Counter::reload();
        }
    }
}
