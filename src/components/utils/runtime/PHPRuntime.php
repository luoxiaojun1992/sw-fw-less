<?php

namespace SwFwLess\components\utils\runtime;

use SwFwLess\components\utils\runtime\php\FFI;

/**
 * @deprecated
 */
class PHPRuntime
{
    public static function supportFFI()
    {
        return FFI::support();
    }
}
