<?php

namespace App\facades;

use App\components\RedisWrapper;

/**
 * Class RedisPool
 *
 * @method static string getKey($key)
 * @method static RedisWrapper pick()
 * @method static release($redis)
 * @method static RedisWrapper getConnect($needRelease = true)
 * @method static RedisWrapper handleRollbackException($redis, \RedisException $e)
 * @method static int countPool()
 * @package App\facades
 */
class RedisPool extends AbstractFacade
{
    protected static function getAccessor()
    {
        return \App\components\RedisPool::create();
    }
}
