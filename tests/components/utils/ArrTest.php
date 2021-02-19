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
}
