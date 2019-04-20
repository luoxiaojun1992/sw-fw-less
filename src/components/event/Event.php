<?php

namespace SwFwLess\components\event;

use SwFwLess\components\traits\Singleton;
use Cake\Event\EventManager;

class Event
{
    private $eventManager;

    use Singleton;

    public function __construct()
    {
        $this->eventManager = EventManager::instance();

        foreach (config('events') as $eventName => $eventListeners) {
            foreach ($eventListeners as $eventListener) {
                $this->eventManager->on($eventName, [], $eventListener);
            }
        }
    }

    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->eventManager, $name], $arguments);
    }
}
