<?php

namespace SwFwLess\components\utils;

use SwFwLess\components\utils\runtime\PHPRuntime;

/**
 * @deprecated
 */
class Runtime
{
    public static function supportFFI()
    {
        return PHPRuntime::supportFFI();
    }
}
