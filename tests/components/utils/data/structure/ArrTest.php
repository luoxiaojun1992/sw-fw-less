<?php

namespace SwFwLessTests;

use SwFwLess\components\utils\data\structure\Arr;

class ArrTest extends \PHPUnit\Framework\TestCase
{
    public function testArrSetWithoutNull()
    {
        $arr = ['foo' => 'bar'];
        Arr::arrSetWithoutNull($arr, 'foo', null);
        $this->assertEquals(
            'bar',
            $arr['foo']
        );

        $arr = ['foo' => 'bar'];
        Arr::arrSetWithoutNull($arr, 'foo', 'newBar');
        $this->assertEquals(
            'newBar',
            $arr['foo']
        );

        $arr = ['foo' => 'bar'];
        Arr::arrSet($arr, 'foo', 'newBar2');
        $this->assertEquals(
            'newBar2',
            $arr['foo']
        );

        $arr = ['foo' => 'bar'];
        Arr::arrSet($arr, 'foo', null);
        $this->assertNull(
            $arr['foo']
        );
    }

    public function testIntVal()
    {
        $intArr = Arr::intVal(['bar' => '1', 'foo' => '2']);
        $this->assertTrue(
            $intArr === ['bar' => 1, 'foo' => 2]
        );

        $intArr = Arr::intVal(['foo' => '1', 'bar' => '2']);
        $this->assertTrue(
            $intArr === ['foo' => 1, 'bar' => 2]
        );
    }

    public function testMapping()
    {
        $origArr = [
            ['col1' => 'row1 col1 value', 'col2' => 'row1 col2 value'],
            ['col1' => 'row2 col1 value', 'col2' => 'row2 col2 value'],
        ];

        $this->assertEquals([
            'row1 col1 value' => 'row1 col2 value',
            'row2 col1 value' => 'row2 col2 value',
        ], Arr::mapping($origArr, 'col1', 'col2'));

        $this->assertEquals([
            'row1 col1 value' => ['col1' => 'row1 col1 value', 'col2' => 'row1 col2 value'],
            'row2 col1 value' => ['col1' => 'row2 col1 value', 'col2' => 'row2 col2 value'],
        ], Arr::mapping($origArr, 'col1'));
    }
}
