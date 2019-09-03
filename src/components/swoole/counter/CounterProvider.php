<?php

namespace SwFwLess\components\swoole\counter;

use SwFwLess\components\provider\AbstractProvider;
use SwFwLess\components\provider\AppProvider;
use SwFwLess\components\provider\WorkerProvider;

class CounterProvider extends AbstractProvider implements AppProvider, WorkerProvider
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
