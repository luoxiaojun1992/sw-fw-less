<?php

namespace SwFwLess\facades;

use Cake\Event\EventManager;

/**
 * Class Event
 *
 * @method static \Cake\Event\Event dispatch($event)
 * @method static EventManager on($eventKey = null, $options = [], $callable = null)
 * @package SwFwLess\facades
 */
class Event extends AbstractFacade
{
    /**
     * @return \SwFwLess\components\event\Event|null
     * @throws \Exception
     */
    protected static function getAccessor()
    {
        return \SwFwLess\components\event\Event::create();
    }
}
