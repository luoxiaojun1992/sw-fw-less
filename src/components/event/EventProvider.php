<?php

namespace SwFwLess\components\event;

use SwFwLess\components\provider\AbstractProvider;
use SwFwLess\components\provider\WorkerProvider;

class EventProvider extends AbstractProvider implements WorkerProvider
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
