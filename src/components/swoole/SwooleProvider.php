<?php

namespace SwFwLess\components\swoole;

use SwFwLess\components\provider\AbstractProvider;
use SwFwLess\components\provider\AppProvider;

class SwooleProvider extends AbstractProvider implements AppProvider
{
    /**
     * @throws \Exception
     */
    public static function bootApp()
    {
        parent::bootApp();

        $coroutineConfig = config('coroutine');
        if ($coroutineConfig['enable_preemptive_scheduler']) {
            throw new \Exception('Preemptive coroutine scheduler has not been supported.');
        }
        \Co::set($coroutineConfig);
    }
}
