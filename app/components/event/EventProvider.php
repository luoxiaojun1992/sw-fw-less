<?php

namespace App\components\event;

use App\components\provider\AbstractProvider;
use App\components\provider\RequestProvider;

class EventProvider extends AbstractProvider implements RequestProvider
{
    /**
     * @throws \Exception
     */
    public static function bootRequest()
    {
        parent::bootRequest();

        Event::create();
    }
}
