<?php

namespace App\facades;

use App\components\redis\RedisWrapper;

/**
 * Class RedisPool
 *
 * @method static RedisWrapper pick($connectionName = null)
 * @method static release($redis)
 * @method static RedisWrapper getConnect($needRelease = true, $connectionName = null)
 * @method static RedisWrapper handleRollbackException($redis, \RedisException $e)
 * @method static int countPool()
 * @package App\facades
 */
class RedisPool extends AbstractFacade
{
    protected static function getAccessor()
    {
        return \App\components\redis\RedisPool::create();
    }
}
