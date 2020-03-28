<?php

namespace SwFwLess\components\event;

use SwFwLess\components\provider\AbstractProvider;

class EventProvider extends AbstractProvider
{
    /**
     * @throws \Exception
     */
    public static function bootWorker()
    {
        parent::bootWorker();

        Event::create();
    }
}
