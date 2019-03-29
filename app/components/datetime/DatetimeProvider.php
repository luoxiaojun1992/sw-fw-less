<?php

namespace App\components\datetime;

use App\components\provider\AbstractProvider;
use App\components\provider\AppProvider;

class DatetimeProvider extends AbstractProvider implements AppProvider
{
    public static function bootApp()
    {
        parent::bootApp();

        $timezone = config('timezone');
        if (date_default_timezone_get() != $timezone) {
            date_default_timezone_set($timezone);
        }
    }
}
