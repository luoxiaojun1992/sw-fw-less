<?php

namespace SwFwLess\facades;

/**
 * Class HealthCheck
 *
 * @method static bool status()
 *
 * @package SwFwLess\facades
 */
class HealthCheck extends AbstractFacade
{
    protected static function getAccessor()
    {
        return \SwFwLess\components\runtime\framework\health\HealthCheck::create();
    }
}
