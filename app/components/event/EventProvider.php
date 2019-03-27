<?php

namespace App\components\event;

use App\components\provider\AbstractProvider;
use App\components\provider\AppProvider;

class EventProvider extends AbstractProvider implements AppProvider
{
    public static function bootApp()
    {
        parent::bootApp();

        foreach (config('events') as $eventName => $eventListeners) {
            foreach ($eventListeners as $eventListener) {
                \App\facades\Event::on($eventName, [], $eventListener);
            }
        }
    }
}
