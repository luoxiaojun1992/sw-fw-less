<?php

namespace SwFwLess\facades;

use SwFwLess\components\swoole\Server;

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
        return \SwFwLess\components\runtime\framework\HealthCheck::create(
            Server::getInstance(),
            \SwFwLess\components\functions\config('server')
        );
    }
}
