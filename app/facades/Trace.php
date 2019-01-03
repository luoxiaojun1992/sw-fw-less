<?php

namespace App\facades;

/**
 * Class Trace
 *
 * @method static span($options, $callback)
 * @package App\facades
 */
class Trace extends AbstractFacade
{
    protected static function getAccessor()
    {
        return \App\components\Trace::create();
    }
}
