<?php

namespace SwFwLessTests;

use SwFwLess\components\utils\data\structure\Arr;

class ArrTest extends \PHPUnit\Framework\TestCase
{
    public function testArrSet()
    {
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

        $this->assertEquals([
            'row1 col2 value',
            'row2 col2 value',
        ], Arr::mapping($origArr, null, 'col2'));

        $this->assertEquals([
            ['col1' => 'row1 col1 value', 'col2' => 'row1 col2 value'],
            ['col1' => 'row2 col1 value', 'col2' => 'row2 col2 value'],
        ], Arr::mapping($origArr));
    }

    public function testMappingFilter()
    {
        $origArr = [
            ['col1' => 'row1 col1 value', 'col2' => 'row1 col2 value'],
            ['col1' => 'row2 col1 value', 'col2' => 'row2 col2 value'],
        ];

        $this->assertEquals([
            'row1 col1 value' => 'row1 col2 value',
            'row2 col1 value' => 'row2 col2 value',
        ], Arr::mappingFilter(
            $origArr,
            function ($row) {
                return true;
            },
            'col1', 'col2'
        ));

        $this->assertEquals([
            'row1 col1 value' => ['col1' => 'row1 col1 value', 'col2' => 'row1 col2 value'],
            'row2 col1 value' => ['col1' => 'row2 col1 value', 'col2' => 'row2 col2 value'],
        ], Arr::mappingFilter(
            $origArr,
            function ($row) {
                return true;
            }, 'col1'
        ));

        $this->assertEquals([
            'row1 col2 value',
            'row2 col2 value',
        ], Arr::mappingFilter(
            $origArr,
            function ($row) {
                return true;
            },
            null, 'col2'
        ));

        $this->assertEquals([
            ['col1' => 'row1 col1 value', 'col2' => 'row1 col2 value'],
            ['col1' => 'row2 col1 value', 'col2' => 'row2 col2 value'],
        ], Arr::mappingFilter(
            $origArr,
            function ($row) {
                return true;
            }
        ));

        $this->assertEquals([], Arr::mappingFilter(
            $origArr,
            function ($row) {
                return false;
            },
            'col1', 'col2'
        ));

        $this->assertEquals([], Arr::mappingFilter(
            $origArr,
            function ($row) {
                return false;
            }, 'col1'
        ));

        $this->assertEquals([], Arr::mappingFilter(
            $origArr,
            function ($row) {
                return false;
            },
            null, 'col2'
        ));

        $this->assertEquals([], Arr::mappingFilter(
            $origArr,
            function ($row) {
                return false;
            }
        ));

        $this->assertEquals([
            'row1 col1 value' => 'row1 col2 value',
        ], Arr::mappingFilter(
            $origArr,
            function ($row) {
                return $row['col2'] !== 'row2 col2 value';
            },
            'col1', 'col2'
        ));

        $this->assertEquals([
            'row1 col1 value' => ['col1' => 'row1 col1 value', 'col2' => 'row1 col2 value'],
        ], Arr::mappingFilter(
            $origArr,
            function ($row) {
                return $row['col2'] !== 'row2 col2 value';
            }, 'col1'
        ));

        $this->assertEquals([
            'row1 col2 value',
        ], Arr::mappingFilter(
            $origArr,
            function ($row) {
                return $row['col2'] !== 'row2 col2 value';
            },
            null, 'col2'
        ));

        $this->assertEquals([
            ['col1' => 'row1 col1 value', 'col2' => 'row1 col2 value'],
        ], Arr::mappingFilter(
            $origArr,
            function ($row) {
                return $row['col2'] !== 'row2 col2 value';
            }
        ));
    }

    public function testDimension()
    {
        $this->assertEquals(1, Arr::dimension(['a', 'b', 'c']));
        $this->assertEquals(2, Arr::dimension(['a', ['b' => 'c'], 'd']));
        $this->assertEquals(3, Arr::dimension(['a', ['b' => 'c'], ['d' => ['e' => 'f']]]));
    }

    public function testTopN()
    {
        $this->assertEquals([3, 2, 5], Arr::topN([3, 1, 7, 8, 2, 6, 5], 3));
        $this->assertEquals([1, 5, 2], Arr::topN([6, 11, 7, 3, 2, 8, 5], 3));
        $this->assertEquals([3, 1], Arr::topN([6, 11, 7, 18, 2, 8, 5], 2));
    }
}
