<?php

namespace SwFwLess\components\datetime;

use SwFwLess\components\provider\AbstractProvider;
use SwFwLess\components\provider\WorkerProvider;

class DatetimeProvider extends AbstractProvider implements WorkerProvider
{
    public static function bootWorker()
    {
        parent::bootWorker();

        $timezone = config('timezone');
        if ((!is_null($timezone)) && (date_default_timezone_get() != $timezone)) {
            date_default_timezone_set($timezone);
        }
    }
}
