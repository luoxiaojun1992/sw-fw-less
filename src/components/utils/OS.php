<?php

namespace SwFwLess\components\utils;

class OS
{
    const OS_LINUX = 'Linux';
    const OS_DARWIN = 'Darwin';
    const OS_UNKNOWN = 'Unknown';

    const UNAME_LINUX = 'Linux';
    const UNAME_DARWIN = 'Darwin';

    public static function type()
    {
        if (static::isLinux()) {
            return static::OS_LINUX;
        } elseif (static::isDarwin()) {
            return static::OS_DARWIN;
        } else {
            return static::OS_UNKNOWN;
        }
    }

    public static function matchType($type)
    {
        return substr(PHP_OS, 0, strlen($type)) === $type;
    }

    public static function isLinux()
    {
        return static::matchType(static::UNAME_LINUX);
    }

    public static function isDarwin()
    {
        return static::matchType(static::UNAME_DARWIN);
    }
}
