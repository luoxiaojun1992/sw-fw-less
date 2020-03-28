<?php

namespace SwFwLess\components\swoole;

use SwFwLess\components\provider\AbstractProvider;

class SwooleProvider extends AbstractProvider
{
    /**
     * @throws \Exception
     */
    protected static function setCoroutineConfig()
    {
        $coroutineConfig = config('coroutine');
        if ($coroutineConfig['enable_preemptive_scheduler']) {
            throw new \Exception('Preemptive coroutine scheduler has not been supported.');
        }
        \Co::set($coroutineConfig);
    }

    /**
     * @throws \Exception
     */
    public static function bootApp()
    {
        parent::bootApp();

        self::setCoroutineConfig();
    }

    /**
     * @throws \Exception
     */
    public static function bootWorker()
    {
        parent::bootWorker();

        self::setCoroutineConfig();
    }
}
