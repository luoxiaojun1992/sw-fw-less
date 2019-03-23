<?php

namespace App\components\event;

use App\components\core\AbstractProvider;
use App\components\core\AppProvider;

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
