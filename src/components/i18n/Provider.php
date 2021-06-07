<?php

namespace SwFwLess\components\i18n;

use SwFwLess\components\provider\WorkerProviderContract;

class Provider implements WorkerProviderContract
{
    public static function bootWorker()
    {
        Translator::create(
            APP_BASE_PATH . 'resources/i18n',
            \SwFwLess\components\functions\config('i18n', [])
        );
    }

    public static function shutdownWorker()
    {
        //
    }
}
