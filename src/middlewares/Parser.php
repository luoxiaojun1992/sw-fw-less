<?php

namespace SwFwLess\middlewares;

use SwFwLess\components\swoole\Scheduler;

class Parser
{
    static $cachedMiddlewareName = [];

    static $cachedMiddlewareNameCount = 0;

    static $cachedMiddlewareNameCapacity = 100;

    /**
     * @param $middlewareName
     * @return array
     */
    public static function parseMiddlewareName($middlewareName)
    {
        return Scheduler::withoutPreemptive(function () use ($middlewareName) {
            if (isset(self::$cachedMiddlewareName[$middlewareName])) {
                $result = self::$cachedMiddlewareName[$middlewareName];
            } else {
                $result = (strpos($middlewareName, ':') > 0) ?
                    explode(':', $middlewareName) :
                    [$middlewareName, null];

                self::$cachedMiddlewareName[$middlewareName] = $result;
                ++self::$cachedMiddlewareNameCount;
                if (self::$cachedMiddlewareNameCount > static::$cachedMiddlewareNameCapacity) {
                    self::$cachedMiddlewareName = array_slice(
                        self::$cachedMiddlewareName, -1 * static::$cachedMiddlewareNameCapacity, null, true
                    );
                    self::$cachedMiddlewareNameCount = static::$cachedMiddlewareNameCapacity;
                }
            }

            $result[0] = \SwFwLess\components\functions\config('middleware.aliases')[$result[0]] ??
                $result[0];

            return $result;
        });
    }
}
