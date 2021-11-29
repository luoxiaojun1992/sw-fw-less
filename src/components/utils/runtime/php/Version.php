<?php

namespace SwFwLess\components\utils\runtime\php;

class Version
{
    const LESS_THAN = '<';
    const LESS_THAN_OR_EQUALS = '<=';
    const GREATER_THAN = '>';
    const GREATER_THAN_OR_EQUALS = '>=';
    const EQUALS = '==';
    const NOT_EQUALS = '!=';

    public static function lessThan($version, $phpVersion = PHP_VERSION)
    {
        return static::compare($version, static::LESS_THAN, $phpVersion);
    }

    public static function lessThanOrEquals($version, $phpVersion = PHP_VERSION)
    {
        return static::compare($version, static::LESS_THAN_OR_EQUALS, $phpVersion);
    }

    public static function greaterThan($version, $phpVersion = PHP_VERSION)
    {
        return static::compare($version, static::GREATER_THAN, $phpVersion);
    }

    public static function greaterThanOrEquals($version, $phpVersion = PHP_VERSION)
    {
        return static::compare($version, static::GREATER_THAN_OR_EQUALS, $phpVersion);
    }

    public static function equals($version, $phpVersion = PHP_VERSION)
    {
        return static::compare($version, static::EQUALS, $phpVersion);
    }

    public static function notEquals($version, $phpVersion = PHP_VERSION)
    {
        return static::compare($version, static::NOT_EQUALS, $phpVersion);
    }

    public static function compare($version, $operator = self::EQUALS, $phpVersion = PHP_VERSION)
    {
        return version_compare($phpVersion, $version, $operator);
    }
}
