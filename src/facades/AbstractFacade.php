<?php

namespace SwFwLess\facades;

use SwFwLess\components\swoole\Scheduler;

abstract class AbstractFacade
{
    protected static $useCache = false;

    protected static $accessorCache = [];

    protected static $accessorCacheCount = 0;

    protected static $accessorCacheCapacity = 100;

    /**
     * @return mixed
     */
    protected static function getAccessor()
    {
        return null;
    }

    protected static function resolveAccessor()
    {
        if (!static::$useCache) {
            return static::getAccessor();
        }

        return Scheduler::withoutPreemptive(function () {
            $accessorId = static::class;
            if (array_key_exists($accessorId, static::$accessorCache)) {
                return static::$accessorCache[$accessorId];
            } else {
                $accessor = static::getAccessor();
                static::$accessorCache[$accessorId] = $accessor;
                ++static::$accessorCacheCount;
                if (static::$accessorCacheCount > static::$accessorCacheCapacity) {
                    static::$accessorCache = array_slice(
                        static::$accessorCache, -1 * static::$accessorCacheCapacity, null, true
                    );
                    static::$accessorCacheCount = static::$accessorCacheCapacity;
                }
                return $accessor;
            }
        });
    }

    public static function __callStatic($name, $arguments)
    {
        $accessor = static::resolveAccessor();
        if ($accessor) {
            return call_user_func_array([$accessor, $name], $arguments);
        }

        return null;
    }
}
