<?php

namespace App\facades;

use App\components\amqp\ConnectionWrapper;

/**
 * Class AMQPConnectionPool
 *
 * @method static string getQueue($name)
 * @method static ConnectionWrapper pick()
 * @method static release($connection)
 * @method static ConnectionWrapper getConnect($needRelease = true)
 * @method static int countPool()
 * @package App\facades
 */
class AMQPConnectionPool extends AbstractFacade
{
    protected static function getAccessor()
    {
        return \App\components\amqp\ConnectionPool::create();
    }
}
