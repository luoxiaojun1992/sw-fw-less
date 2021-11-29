<?php

namespace SwFwLessTest\components\utils\runtime\php;

use PHPUnit\Framework\TestCase;
use SwFwLess\components\utils\runtime\php\FFI;

class FFITest extends TestCase
{
    public function testSupport()
    {
        $this->assertFalse(FFI::support('7.1.0'));

        if (class_exists('\FFI')) {
            $this->assertTrue(FFI::support('7.4.0'));
        } else {
            $this->assertFalse(FFI::support('7.4.0'));
        }
    }
}
