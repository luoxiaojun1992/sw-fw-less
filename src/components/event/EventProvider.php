<?php

namespace SwFwLess\components\event;

use SwFwLess\components\provider\AbstractProvider;
use SwFwLess\components\provider\RequestProvider;

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
