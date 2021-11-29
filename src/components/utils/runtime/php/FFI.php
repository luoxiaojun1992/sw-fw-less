<?php

namespace SwFwLess\components\utils\runtime\php;

class FFI
{
    public static function support($phpVersion = PHP_VERSION)
    {
        return (Version::greaterThanOrEquals('7.4.0', $phpVersion)) &&
            class_exists('\FFI');
    }
}
