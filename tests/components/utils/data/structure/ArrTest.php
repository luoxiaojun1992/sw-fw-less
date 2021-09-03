<?php

namespace SwFwLessTests\components\utils\data\structure;

use SwFwLess\components\utils\data\structure\Arr;
use SwFwLess\components\utils\data\structure\variable\MetasyntacticVars;

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
        $this->assertTrue(
            ['foo' => 'newBar2'] ===
            $arr
        );

        $arr = ['foo' => 'bar'];
        Arr::arrSet($arr, 'foo', null);
        $this->assertNull(
            $arr['foo']
        );
        $this->assertTrue(
            ['foo' => null] ===
            $arr
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
        $this->assertTrue(
            ['foo' => 'bar'] ===
            $arr
        );

        $arr = ['foo' => 'bar'];
        Arr::arrSetWithoutNull($arr, 'foo', 'newBar');
        $this->assertEquals(
            'newBar',
            $arr['foo']
        );
        $this->assertTrue(
            ['foo' => 'newBar'] ===
            $arr
        );
    }

    public function testNestedArrSet()
    {
        $arr = [
            MetasyntacticVars::FOO => 1, MetasyntacticVars::BAR => 2, MetasyntacticVars::QUX => 4
        ];
        Arr::nestedArrSet($arr, MetasyntacticVars::BAZ, 3);
        $this->assertTrue(
            [
                MetasyntacticVars::FOO => 1, MetasyntacticVars::BAR => 2,
                MetasyntacticVars::QUX => 4, MetasyntacticVars::BAZ => 3
            ] ===
            $arr
        );

        $arr = [
            'foo' => ['bar' => ['baz' => ['qux' => 1]]],
            'bar' => ['foo' => ['baz' => ['qux' => 2]]],
            'baz' => ['foo' => ['bar' => ['qux' => 3]]],
            'qux' => 4
        ];
        Arr::nestedArrSet($arr, 'bar.foo', ['baz' => ['qux' => 2]]);
        $this->assertTrue(
            [
                'foo' => ['bar' => ['baz' => ['qux' => 1]]],
                'bar' => ['foo' => ['baz' => ['qux' => 2]]],
                'baz' => ['foo' => ['bar' => ['qux' => 3]]],
                'qux' => 4
            ] ===
            $arr
        );
    }

    public function testNestedArrForget()
    {
        $arr = ['foo' => 1, 'bar' => 2, 'baz' => 3, 'qux' => 4];
        Arr::nestedArrForget($arr, 'foo');
        $this->assertTrue(
            ['bar' => 2, 'baz' => 3, 'qux' => 4] ===
            $arr
        );

        $arr = [
            'foo' => ['bar' => ['baz' => ['qux' => 1]]],
            'bar' => ['foo' => ['baz' => ['qux' => 2]]],
            'baz' => ['foo' => ['bar' => ['qux' => 3]]],
            'qux' => 4
        ];
        Arr::nestedArrForget($arr, 'bar.foo');
        $this->assertTrue(
            [
                'foo' => ['bar' => ['baz' => ['qux' => 1]]],
                'bar' => [],
                'baz' => ['foo' => ['bar' => ['qux' => 3]]],
                'qux' => 4
            ] ===
            $arr
        );
    }

    public function testArrayColumnUnique()
    {
        $this->assertTrue(
            [
                ['a' => 1],
                ['a' => 2],
                ['a' => 3],
                ['a' => 5],
                ['a' => 6],
                ['a' => 7],
            ] ===
            Arr::arrayColumnUnique([
                ['a' => 1],
                ['a' => 2],
                ['a' => 2],
                ['a' => 3],
                ['a' => 5],
                ['a' => 5],
                ['a' => 6],
                ['a' => 7],
            ], 'a', false)
        );

        $this->assertTrue(
            [
                0 => ['a' => 1],
                1 => ['a' => 2],
                3 => ['a' => 3],
                5 => ['a' => 5],
                6 => ['a' => 6],
                7 => ['a' => 7],
            ] ===
            Arr::arrayColumnUnique([
                ['a' => 1],
                ['a' => 2],
                ['a' => 2],
                ['a' => 3],
                ['a' => 3],
                ['a' => 5],
                ['a' => 6],
                ['a' => 7],
            ], 'a', true)
        );
    }

    public function testIntVal()
    {
        $intArr = Arr::intVal(['bar' => '1', 'foo' => '2']);
        $this->assertTrue(
            ['bar' => 1, 'foo' => 2] ===
            $intArr
        );

        $intArr = Arr::intVal(['foo' => '1', 'bar' => '2']);
        $this->assertTrue(
            ['foo' => 1, 'bar' => 2] ===
            $intArr
        );
    }

    public function testDoubleVal()
    {
        $intArr = Arr::doubleVal(['bar' => '1', 'foo' => '2']);
        $this->assertTrue(
            ['bar' => 1.0, 'foo' => 2.0] ===
            $intArr
        );

        $intArr = Arr::doubleVal(['foo' => '1', 'bar' => '2']);
        $this->assertTrue(
            ['foo' => 1.0, 'bar' => 2.0] ===
            $intArr
        );
    }

    public function testStringVal()
    {
        $strArr = Arr::stringVal(['bar' => 1, 'foo' => 2]);
        $this->assertTrue(
            ['bar' => '1', 'foo' => '2'] ===
            $strArr
        );

        $strArr = Arr::stringVal(['foo' => 1, 'bar' => 2]);
        $this->assertTrue(
            ['foo' => '1', 'bar' => '2'] ===
            $strArr
        );
    }

    public function testMapping()
    {
        $origArr = [
            ['col1' => 'row1 col1 value', 'col2' => 'row1 col2 value'],
            ['col1' => 'row2 col1 value', 'col2' => 'row2 col2 value'],
        ];

        $this->assertTrue([
            'row1 col1 value' => 'row1 col2 value',
            'row2 col1 value' => 'row2 col2 value',
        ] === Arr::mapping($origArr, 'col1', 'col2'));

        $this->assertTrue([
            'row1 col1 value' => ['col1' => 'row1 col1 value', 'col2' => 'row1 col2 value'],
            'row2 col1 value' => ['col1' => 'row2 col1 value', 'col2' => 'row2 col2 value'],
        ] === Arr::mapping($origArr, 'col1'));

        $this->assertTrue([
            'row1 col2 value',
            'row2 col2 value',
        ] === Arr::mapping($origArr, null, 'col2'));

        $this->assertTrue([
            ['col1' => 'row1 col1 value', 'col2' => 'row1 col2 value'],
            ['col1' => 'row2 col1 value', 'col2' => 'row2 col2 value'],
        ] === Arr::mapping($origArr));
    }

    public function testMappingFilter()
    {
        $origArr = [
            ['col1' => 'row1 col1 value', 'col2' => 'row1 col2 value'],
            ['col1' => 'row2 col1 value', 'col2' => 'row2 col2 value'],
        ];

        $this->assertTrue([
            'row1 col1 value' => 'row1 col2 value',
            'row2 col1 value' => 'row2 col2 value',
        ] === Arr::mappingFilter(
            $origArr,
            function ($row) {
                return true;
            },
            'col1', 'col2'
        ));

        $this->assertTrue([
            'row1 col1 value' => ['col1' => 'row1 col1 value', 'col2' => 'row1 col2 value'],
            'row2 col1 value' => ['col1' => 'row2 col1 value', 'col2' => 'row2 col2 value'],
        ] === Arr::mappingFilter(
            $origArr,
            function ($row) {
                return true;
            }, 'col1'
        ));

        $this->assertTrue([
            'row1 col2 value',
            'row2 col2 value',
        ] === Arr::mappingFilter(
            $origArr,
            function ($row) {
                return true;
            },
            null, 'col2'
        ));

        $this->assertTrue([
            ['col1' => 'row1 col1 value', 'col2' => 'row1 col2 value'],
            ['col1' => 'row2 col1 value', 'col2' => 'row2 col2 value'],
        ] === Arr::mappingFilter(
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
        $this->assertTrue([3, 2, 5] === Arr::topN([3, 1, 7, 8, 2, 6, 5], 3));
        $this->assertTrue([1, 5, 2] === Arr::topN([6, 11, 7, 3, 2, 8, 5], 3));
        $this->assertTrue([3, 1] === Arr::topN([6, 11, 7, 18, 2, 8, 5], 2));
    }

    public function testIsAssoc()
    {
        $this->assertFalse(Arr::isAssoc([MetasyntacticVars::FOO, MetasyntacticVars::BAR]));
        $this->assertFalse(Arr::isAssoc(
            [
                1 => MetasyntacticVars::FOO,
                2 => MetasyntacticVars::BAR
            ]
        ));
        $this->assertFalse(Arr::isAssoc(
            [
                '0' => MetasyntacticVars::FOO,
                '1' => MetasyntacticVars::BAR
            ]
        ));
        $this->assertFalse(Arr::isAssoc(
            [
                '1' => MetasyntacticVars::FOO,
                '2' => MetasyntacticVars::BAR
            ]
        ));
        $this->assertTrue(
            Arr::isAssoc([
                MetasyntacticVars::FOO => MetasyntacticVars::FOO,
                MetasyntacticVars::BAR => MetasyntacticVars::BAR,
            ])
        );
        $this->assertTrue(
            Arr::isAssoc([
                MetasyntacticVars::FOO => MetasyntacticVars::FOO,
                2 => MetasyntacticVars::BAR,
            ])
        );
    }

    public function testSwapNums()
    {
        $this->assertTrue(
            [2, 1] ===
            Arr::swapNums([1, 2], 0, 1)
        );
        $this->assertTrue(
            [-2, -1] ===
            Arr::swapNums([-1, -2], 0, 1)
        );
        $this->assertTrue(
            [2, -1] ===
            Arr::swapNums([-1, 2], 0, 1)
        );
        $this->assertTrue(
            [-2, 1] ===
            Arr::swapNums([1, -2], 0, 1)
        );
        $this->assertTrue(
            [0, 0] ===
            Arr::swapNums([0, 0], 0, 1)
        );
        $this->assertTrue(
            [0, 1] ===
            Arr::swapNums([1, 0], 0, 1)
        );
        $this->assertTrue(
            [0, -1] ===
            Arr::swapNums([-1, 0], 0, 1)
        );
        $this->assertTrue(
            [2, 0] ===
            Arr::swapNums([0, 2], 0, 1)
        );
        $this->assertTrue(
            [-2, 0] ===
            Arr::swapNums([0, -2], 0, 1)
        );
    }
}
