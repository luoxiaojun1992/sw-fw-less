<?php

class HelperTest extends \PHPUnit\Framework\TestCase
{
    protected static $testSetArr;

    protected static $testForgetArr;

    public function testNestedArrHas()
    {
        $arr = ['a' => ['b' => 'c', 'g' => 'h'], 'd' => ['e' => 'f', 'i' => 'j']];
        $this->assertTrue(\SwFwLess\components\Helper::nestedArrHas($arr, 'a.g'));
        $this->assertFalse(\SwFwLess\components\Helper::nestedArrHas($arr, 'a.e'));
    }

    public function testNestedArrGet()
    {
        $arr = ['a' => ['b' => 'c', 'g' => 'h'], 'd' => ['e' => 'f', 'i' => 'j']];
        $this->assertEquals('h', \SwFwLess\components\Helper::nestedArrGet($arr, 'a.g'));
    }

    public function testNestedArrSet()
    {
        $arr = ['a' => ['b' => 'c', 'g' => 'h'], 'd' => ['e' => 'f', 'i' => 'j']];
        \SwFwLess\components\Helper::nestedArrSet($arr, 'a.k', 'l');
        $this->assertEquals(['a' => ['b' => 'c', 'g' => 'h', 'k' => 'l'], 'd' => ['e' => 'f', 'i' => 'j']], $arr);

        $arr2 = ['a' => ['b' => 'c', 'g' => 'h'], 'd' => ['e' => 'f', 'i' => 'j']];
        \SwFwLess\components\Helper::nestedArrSet($arr2, 'a.b.d', 'l');
        $this->assertEquals(['a' => ['b' => 'c', 'g' => 'h'], 'd' => ['e' => 'f', 'i' => 'j']], $arr2);

        $arr3 = ['a' => ['b' => 'c', 'g' => 'h'], 'd' => ['e' => 'f', 'i' => 'j']];
        \SwFwLess\components\Helper::nestedArrSet($arr3, 'c.k', 'l');
        $this->assertEquals(['a' => ['b' => 'c', 'g' => 'h'], 'd' => ['e' => 'f', 'i' => 'j'], 'c' => ['k' => 'l']], $arr3);

        $arr4 = ['a' => ['b' => 'c', 'g' => 'h'], 'd' => ['e' => 'f', 'i' => 'j']];
        \SwFwLess\components\Helper::nestedArrSet($arr4, 'c', 'l');
        $this->assertEquals(['a' => ['b' => 'c', 'g' => 'h'], 'd' => ['e' => 'f', 'i' => 'j'], 'c' => 'l'], $arr4);

        static::$testSetArr = ['a' => ['b' => 'c', 'g' => 'h'], 'd' => ['e' => 'f', 'i' => 'j']];
        \SwFwLess\components\Helper::nestedArrSet(static::$testSetArr, 'a.k', 'l');
        $this->assertEquals(['a' => ['b' => 'c', 'g' => 'h', 'k' => 'l'], 'd' => ['e' => 'f', 'i' => 'j']], static::$testSetArr);
        static::$testSetArr = null;
    }

    public function testNestedArrForget()
    {
        $arr = ['a' => ['b' => 'c', 'g' => 'h'], 'd' => ['e' => 'f', 'i' => 'j']];
        \SwFwLess\components\Helper::nestedArrForget($arr, 'd.e');
        $this->assertEquals(['a' => ['b' => 'c', 'g' => 'h'], 'd' => ['i' => 'j']], $arr);

        static::$testForgetArr = ['a' => ['b' => 'c', 'g' => 'h'], 'd' => ['e' => 'f', 'i' => 'j']];
        \SwFwLess\components\Helper::nestedArrForget(static::$testForgetArr, 'd.e');
        $this->assertEquals(['a' => ['b' => 'c', 'g' => 'h'], 'd' => ['i' => 'j']], static::$testForgetArr);
        static::$testForgetArr = null;
    }
}
