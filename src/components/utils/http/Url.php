<?php

namespace SwFwLess\components\utils\http;

use SwFwLess\components\swoole\Scheduler;

class Url
{
    protected static $decodedCache = [];

    protected static $decodedCacheCount = 0;

    public static function decode($url)
    {
        return Scheduler::withoutPreemptive(function () use ($url) {
            if (isset(self::$decodedCache[$url])) {
                $cachedUrl = self::$decodedCache[$url];
            } else {
                $cachedUrl = rawurldecode($url);
                self::$decodedCache[$url] = $cachedUrl;
                ++self::$decodedCacheCount;
                if (self::$decodedCacheCount > 100) {
                    self::$decodedCache = array_slice(
                        self::$decodedCache, -100, null, true
                    );
                    self::$decodedCacheCount = 100;
                }
            }

            return $cachedUrl;
        });
    }
}
