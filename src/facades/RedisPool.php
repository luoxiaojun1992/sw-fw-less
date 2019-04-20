<?php

namespace SwFwLess\facades;

use SwFwLess\components\redis\RedisWrapper;

/**
 * Class RedisPool
 *
 * @method static RedisWrapper pick($connectionName = null)
 * @method static release($redis)
 * @method static RedisWrapper getConnect($needRelease = true, $connectionName = null)
 * @method static RedisWrapper handleRollbackException($redis, \RedisException $e)
 * @method static int countPool()
 * @package SwFwLess\facades
 */
class RedisPool extends AbstractFacade
{
    protected static function getAccessor()
    {
        return \SwFwLess\components\redis\RedisPool::create();
    }
}
