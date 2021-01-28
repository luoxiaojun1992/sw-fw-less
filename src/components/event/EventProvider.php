<?php

namespace SwFwLess\components\event;

use SwFwLess\components\provider\WorkerProviderContract;

class EventProvider implements WorkerProviderContract
{
    /**
     * @throws \Exception
     */
    public static function bootWorker()
    {
        Event::create();
    }

    public static function shutdownWorker()
    {
        //
    }
}
