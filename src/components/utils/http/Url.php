<?php

namespace SwFwLess\components\utils\http;

use SwFwLess\components\swoole\Scheduler;

class Url
{
    protected static $decodedCache = [];

    protected static $decodedCacheCount = 0;

    protected static $decodedCacheCapacity = 100;

    public static function decode($url)
    {
        return Scheduler::withoutPreemptive(function () use ($url) {
            if (isset(self::$decodedCache[$url])) {
                $cachedUrl = self::$decodedCache[$url];
            } else {
                $cachedUrl = rawurldecode($url);
                self::$decodedCache[$url] = $cachedUrl;
                ++self::$decodedCacheCount;
                if (self::$decodedCacheCount > static::$decodedCacheCapacity) {
                    self::$decodedCache = array_slice(
                        self::$decodedCache, -1 * static::$decodedCacheCapacity, null, true
                    );
                    self::$decodedCacheCount = static::$decodedCacheCapacity;
                }
            }

            return $cachedUrl;
        });
    }
}
