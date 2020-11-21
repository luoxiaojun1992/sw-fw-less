<?php

namespace SwFwLess\components\utils;

class Runtime
{
    public static function supportFFI()
    {
        return version_compare(PHP_VERSION, '7.4.0') >= 0;
    }
}
