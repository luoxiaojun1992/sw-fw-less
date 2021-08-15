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

        $this->assertTrue(\SwFwLess\components\utils\Str::contains('abc', 'A', 0, false));
        $this->assertTrue(\SwFwLess\components\utils\Str::contains('abc', 'B', 0, false));
        $this->assertTrue(\SwFwLess\components\utils\Str::contains('abc', 'C', 0, false));
        $this->assertTrue(\SwFwLess\components\utils\Str::contains('abc', 'Ab', 0, false));
        $this->assertTrue(\SwFwLess\components\utils\Str::contains('abc', 'AB', 0, false));
        $this->assertTrue(\SwFwLess\components\utils\Str::contains('abc', 'aB', 0, false));
        $this->assertTrue(\SwFwLess\components\utils\Str::contains('abc', 'Bc', 0, false));
        $this->assertTrue(\SwFwLess\components\utils\Str::contains('abc', 'BC', 0, false));
        $this->assertTrue(\SwFwLess\components\utils\Str::contains('abc', 'bC', 0, false));
        $this->assertTrue(\SwFwLess\components\utils\Str::contains('abc', 'Abc', 0, false));
        $this->assertTrue(\SwFwLess\components\utils\Str::contains('abc', 'ABc', 0, false));
        $this->assertTrue(\SwFwLess\components\utils\Str::contains('abc', 'ABC', 0, false));
        $this->assertTrue(\SwFwLess\components\utils\Str::contains('abc', 'aBc', 0, false));
        $this->assertTrue(\SwFwLess\components\utils\Str::contains('abc', 'aBC', 0, false));
        $this->assertTrue(\SwFwLess\components\utils\Str::contains('abc', 'abC', 0, false));
        $this->assertFalse(\SwFwLess\components\utils\Str::contains('abc', 'D', 0, false));
        $this->assertFalse(\SwFwLess\components\utils\Str::contains('abc', 'Ac', 0, false));
        $this->assertFalse(\SwFwLess\components\utils\Str::contains('abc', 'AC', 0, false));
        $this->assertFalse(\SwFwLess\components\utils\Str::contains('abc', 'aC', 0, false));

        $this->assertTrue(\SwFwLess\components\utils\Str::contains('abc', '', 0));
        $this->assertTrue(\SwFwLess\components\utils\Str::contains('abc', '', 1));
        $this->assertTrue(\SwFwLess\components\utils\Str::contains('abc', '', 2));
        $this->assertTrue(\SwFwLess\components\utils\Str::contains('abc', 'a', 0));
        $this->assertFalse(\SwFwLess\components\utils\Str::contains('abc', 'a', 1));
        $this->assertFalse(\SwFwLess\components\utils\Str::contains('abc', 'a', 2));
        $this->assertTrue(\SwFwLess\components\utils\Str::contains('abc', 'b', 0));
        $this->assertTrue(\SwFwLess\components\utils\Str::contains('abc', 'b', 1));
        $this->assertFalse(\SwFwLess\components\utils\Str::contains('abc', 'b', 2));
        $this->assertTrue(\SwFwLess\components\utils\Str::contains('abc', 'c', 0));
        $this->assertTrue(\SwFwLess\components\utils\Str::contains('abc', 'c', 1));
        $this->assertTrue(\SwFwLess\components\utils\Str::contains('abc', 'c', 2));
        $this->assertFalse(\SwFwLess\components\utils\Str::contains('abc', 'c', 3));
        $this->assertTrue(\SwFwLess\components\utils\Str::contains('abc', 'ab', 0));
        $this->assertFalse(\SwFwLess\components\utils\Str::contains('abc', 'ab', 1));
        $this->assertTrue(\SwFwLess\components\utils\Str::contains('abc', 'bc', 0));
        $this->assertTrue(\SwFwLess\components\utils\Str::contains('abc', 'bc', 1));
        $this->assertFalse(\SwFwLess\components\utils\Str::contains('abc', 'bc', 2));
        $this->assertTrue(\SwFwLess\components\utils\Str::contains('abc', 'abc', 0));
        $this->assertFalse(\SwFwLess\components\utils\Str::contains('abc', 'abc', 1));

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
