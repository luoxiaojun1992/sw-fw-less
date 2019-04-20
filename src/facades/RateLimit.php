<?php

namespace SwFwLess\facades;

/**
 * Class RateLimit
 *
 * @method static bool pass($metric, $period, $throttle, &$remaining = null)
 * @package SwFwLess\facades
 */
class RateLimit extends AbstractFacade
{
    protected static function getAccessor()
    {
        return \SwFwLess\components\ratelimit\RateLimit::create();
    }
}
