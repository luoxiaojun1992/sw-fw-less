<?php

namespace SwFwLess\facades;

/**
 * Class RateLimit
 *
 * @method static bool pass($metric, $period, $throttle, &$remaining = null)
 * @method static clear($metric)
 * @package SwFwLess\facades
 */
class RateLimit extends AbstractFacade
{
    protected static function getAccessor()
    {
        return \SwFwLess\components\ratelimit\RateLimit::create();
    }
}
