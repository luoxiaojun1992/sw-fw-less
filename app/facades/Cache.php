<?php

namespace App\facades;

/**
 * Class Cache
 *
 * @method static bool set($key, $value, $ttl = 0)
 * @method static bool|string get($key)
 * @package App\facades
 */
class Cache extends AbstractFacade
{
    protected static function getAccessor()
    {
        return \App\components\cache\Cache::create();
    }
}
