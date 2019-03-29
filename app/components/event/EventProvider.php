<?php

namespace App\components\event;

use App\components\provider\AbstractProvider;
use App\components\provider\AppProvider;

class EventProvider extends AbstractProvider implements AppProvider
{
    /**
     * @throws \Exception
     */
    public static function bootApp()
    {
        parent::bootApp();

        Event::create();
    }
}
