<?php

namespace App\facades;

/**
 * Class RateLimit
 *
 * @method static bool pass($metric, $period, $throttle)
 * @package App\facades
 */
class RateLimit extends AbstractFacade
{
    protected static function getAccessor()
    {
        return \App\components\RateLimit::create();
    }
}
