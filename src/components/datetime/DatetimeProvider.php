<?php

namespace SwFwLess\components\datetime;

use SwFwLess\components\provider\AbstractProvider;
use SwFwLess\components\provider\RequestProvider;

class DatetimeProvider extends AbstractProvider implements RequestProvider
{
    public static function bootRequest()
    {
        parent::bootRequest();

        $timezone = config('timezone');
        if (date_default_timezone_get() != $timezone) {
            date_default_timezone_set($timezone);
        }
    }
}
