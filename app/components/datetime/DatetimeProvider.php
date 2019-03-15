<?php

namespace App\components\datetime;

use App\components\core\AbstractProvider;
use App\components\core\AppProvider;

class DatetimeProvider extends AbstractProvider implements AppProvider
{
    public static function bootApp()
    {
        parent::bootApp();

        date_default_timezone_set(config('timezone'));
    }
}
