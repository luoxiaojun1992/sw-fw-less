<?php

class StrTest extends \PHPUnit\Framework\TestCase
{
    public function testContains()
    {
        $this->assertTrue(\SwFwLess\components\utils\Str::contains('abc', ''));
        $this->assertTrue(\SwFwLess\components\utils\Str::contains('abc', 'a'));
        $this->assertTrue(\SwFwLess\components\utils\Str::contains('abc', 'b'));
        $this->assertTrue(\SwFwLess\components\utils\Str::contains('abc', 'c'));
        $this->assertTrue(\SwFwLess\components\utils\Str::contains('abc', 'ab'));
        $this->assertTrue(\SwFwLess\components\utils\Str::contains('abc', 'bc'));
        $this->assertTrue(\SwFwLess\components\utils\Str::contains('abc', 'abc'));
        $this->assertFalse(\SwFwLess\components\utils\Str::contains('abc', 'd'));
        $this->assertFalse(\SwFwLess\components\utils\Str::contains('abc', 'ac'));

        $this->assertTrue(\SwFwLess\components\utils\Str::contains('你好，世界', ''));
        $this->assertTrue(\SwFwLess\components\utils\Str::contains('你好，世界', '你'));
        $this->assertTrue(\SwFwLess\components\utils\Str::contains('你好，世界', '好'));
        $this->assertTrue(\SwFwLess\components\utils\Str::contains('你好，世界', '世'));
        $this->assertTrue(\SwFwLess\components\utils\Str::contains('你好，世界', '界'));
        $this->assertTrue(\SwFwLess\components\utils\Str::contains('你好，世界', '，'));
        $this->assertTrue(\SwFwLess\components\utils\Str::contains('你好，世界', '你好'));
        $this->assertTrue(\SwFwLess\components\utils\Str::contains('你好，世界', '世界'));
        $this->assertFalse(\SwFwLess\components\utils\Str::contains('你好，世界', '！'));
        $this->assertFalse(\SwFwLess\components\utils\Str::contains('你好，世界', '好世界'));

        $this->assertTrue(\SwFwLess\components\utils\Str::contains('你好，世界 Hello World', '世界'));
        $this->assertTrue(\SwFwLess\components\utils\Str::contains('你好，世界 Hello World', '世界 Hello'));
    }
}
