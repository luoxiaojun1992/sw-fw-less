<?php

class HelperTest extends \PHPUnit\Framework\TestCase
{
    protected static $arr;

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

        static::$arr = ['a' => ['b' => 'c', 'g' => 'h'], 'd' => ['e' => 'f', 'i' => 'j']];
        \SwFwLess\components\Helper::nestedArrSet(static::$arr, 'a.k', 'l');
        $this->assertEquals(['a' => ['b' => 'c', 'g' => 'h', 'k' => 'l'], 'd' => ['e' => 'f', 'i' => 'j']], static::$arr);
        static::$arr = null;
    }

    public function testNestedArrForget()
    {
        $arr = ['a' => ['b' => 'c', 'g' => 'h'], 'd' => ['e' => 'f', 'i' => 'j']];
        \SwFwLess\components\Helper::nestedArrForget($arr, 'd.e');
        $this->assertEquals(['a' => ['b' => 'c', 'g' => 'h'], 'd' => ['i' => 'j']], $arr);

        static::$arr = ['a' => ['b' => 'c', 'g' => 'h'], 'd' => ['e' => 'f', 'i' => 'j']];
        \SwFwLess\components\Helper::nestedArrForget(static::$arr, 'd.e');
        $this->assertEquals(['a' => ['b' => 'c', 'g' => 'h'], 'd' => ['i' => 'j']], static::$arr);
        static::$arr = null;
    }
}
