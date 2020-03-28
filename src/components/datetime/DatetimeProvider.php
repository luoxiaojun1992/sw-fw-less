<?php

namespace SwFwLess\components\datetime;

use SwFwLess\components\provider\AbstractProvider;

class DatetimeProvider extends AbstractProvider
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
