<?php

namespace App\facades;

/**
 * Class RedisLock
 *
 * //todo @method
 * @package App\facades
 */
class RedLock extends AbstractFacade
{
    protected static function getAccessor()
    {
        return \App\components\RedLock::create();
    }
}
