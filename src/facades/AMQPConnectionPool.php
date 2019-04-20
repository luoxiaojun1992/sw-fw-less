<?php

namespace SwFwLess\facades;

use SwFwLess\components\amqp\ConnectionWrapper;

/**
 * Class AMQPConnectionPool
 *
 * @method static string getQueue($name)
 * @method static ConnectionWrapper pick()
 * @method static release($connection)
 * @method static ConnectionWrapper getConnect($needRelease = true)
 * @method static int countPool()
 * @package SwFwLess\facades
 */
class AMQPConnectionPool extends AbstractFacade
{
    protected static function getAccessor()
    {
        return \SwFwLess\components\amqp\ConnectionPool::create();
    }
}
