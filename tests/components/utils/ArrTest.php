<?php

class ArrTest extends \PHPUnit\Framework\TestCase
{
    public function testIntVal()
    {
        $intArr = \SwFwLess\components\utils\Arr::intVal(['bar' => '1', 'foo' => '2']);
        $this->assertTrue(
            $intArr === ['bar' => 1, 'foo' => 2]
        );

        $intArr = \SwFwLess\components\utils\Arr::intVal(['foo' => '1', 'bar' => '2']);
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
        ], \SwFwLess\components\utils\Arr::mapping($origArr, 'col1', 'col2'));

        $this->assertEquals([
            'row1 col1 value' => ['col1' => 'row1 col1 value', 'col2' => 'row1 col2 value'],
            'row2 col1 value' => ['col1' => 'row2 col1 value', 'col2' => 'row2 col2 value'],
        ], \SwFwLess\components\utils\Arr::mapping($origArr, 'col1'));
    }
}
