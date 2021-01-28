<?php

namespace SwFwLess\components\datetime;

use SwFwLess\components\provider\WorkerProviderContract;

class DatetimeProvider implements WorkerProviderContract
{
    public static function bootWorker()
    {
        $timezone = \SwFwLess\components\functions\config('timezone');
        if ((!is_null($timezone)) && (date_default_timezone_get() != $timezone)) {
            date_default_timezone_set($timezone);
        }
    }

    public static function shutdownWorker()
    {
        //
    }
}
