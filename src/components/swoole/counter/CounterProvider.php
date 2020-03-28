<?php

namespace SwFwLess\components\swoole\counter;

use SwFwLess\components\provider\AbstractProvider;

class CounterProvider extends AbstractProvider
{
    public static function bootApp()
    {
        parent::bootApp();

        Counter::init();
    }

    public static function bootWorker()
    {
        parent::bootWorker();

        Counter::reload();
    }
}
