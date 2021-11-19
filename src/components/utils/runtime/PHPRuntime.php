<?php

namespace SwFwLess\components\utils\runtime;

class PHPRuntime
{
    public static function supportFFI()
    {
        return (version_compare(PHP_VERSION, '7.4.0') >= 0) &&
            class_exists('\FFI');
    }
}
