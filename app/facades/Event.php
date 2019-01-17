<?php

namespace App\facades;

use Cake\Event\EventManager;

/**
 * Class Event
 *
 * @method static \Cake\Event\Event dispatch($event)
 * @method static EventManager on($eventKey = null, $options = [], $callable = null)
 * @package App\facades
 */
class Event extends AbstractFacade
{
    protected static function getAccessor()
    {
        return EventManager::instance();
    }
}
