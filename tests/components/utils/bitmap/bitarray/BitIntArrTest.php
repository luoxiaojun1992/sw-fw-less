<?php

class BitIntArrTest extends \PHPUnit\Framework\TestCase
{
    public function testPut()
    {
        //Case 1
        $bitIntArr = new \SwFwLess\components\utils\bitmap\bitarray\BitIntArr();

        for ($i = 1; $i <= 10000; ++$i) {
            $bitIntArr->put($i);
        }

        //Case 2
        $bitIntArr = new \SwFwLess\components\utils\bitmap\bitarray\BitIntArr();

        for ($i = 1; $i <= 10000; $i += 2) {
            $bitIntArr->put($i);
        }

        //Case 3
        $bitIntArr = new \SwFwLess\components\utils\bitmap\bitarray\BitIntArr();

        for ($i = 1; $i <= 10000; ++$i) {
            $bitIntArr->put($i);
        }

        for ($i = 20001; $i <= 30000; ++$i) {
            $bitIntArr->put($i);
        }

        //Case 4
        $bitIntArr = new \SwFwLess\components\utils\bitmap\bitarray\BitIntArr();

        for ($i = 1; $i <= 10000; $i += 2) {
            $bitIntArr->put($i);
        }

        for ($i = 20001; $i <= 30000; $i += 2) {
            $bitIntArr->put($i);
        }

        $this->assertTrue(true);
    }

    public function testHas()
    {
        //Case 1
        $bitIntArr = new \SwFwLess\components\utils\bitmap\bitarray\BitIntArr();

        for ($i = 1; $i <= 10000; ++$i) {
            $bitIntArr->put($i);
            $this->assertTrue($bitIntArr->has($i));
        }

        for ($i = 1; $i <= 10000; ++$i) {
            $this->assertTrue($bitIntArr->has($i));
        }

        //Case 2
        $bitIntArr = new \SwFwLess\components\utils\bitmap\bitarray\BitIntArr();

        for ($i = 1; $i <= 10000; $i += 2) {
            $bitIntArr->put($i);
            $this->assertTrue($bitIntArr->has($i));
        }

        for ($i = 1; $i <= 10000; $i += 2) {
            $this->assertTrue($bitIntArr->has($i));
        }

        //Case 3
        $bitIntArr = new \SwFwLess\components\utils\bitmap\bitarray\BitIntArr();

        for ($i = 1; $i <= 10000; ++$i) {
            $bitIntArr->put($i);
            $this->assertTrue($bitIntArr->has($i));
        }

        for ($i = 1; $i <= 10000; ++$i) {
            $this->assertTrue($bitIntArr->has($i));
        }

        for ($i = 20001; $i <= 30000; ++$i) {
            $bitIntArr->put($i);
            $this->assertTrue($bitIntArr->has($i));
        }

        for ($i = 20001; $i <= 30000; ++$i) {
            $this->assertTrue($bitIntArr->has($i));
        }

        //Case 4
        $bitIntArr = new \SwFwLess\components\utils\bitmap\bitarray\BitIntArr();

        for ($i = 1; $i <= 10000; $i += 2) {
            $bitIntArr->put($i);
            $this->assertTrue($bitIntArr->has($i));
        }

        for ($i = 1; $i <= 10000; $i += 2) {
            $this->assertTrue($bitIntArr->has($i));
        }

        for ($i = 20001; $i <= 30000; $i += 2) {
            $bitIntArr->put($i);
            $this->assertTrue($bitIntArr->has($i));
        }

        for ($i = 20001; $i <= 30000; $i += 2) {
            $this->assertTrue($bitIntArr->has($i));
        }
    }

    public function testDelete()
    {
        //Case 1
        $bitIntArr = new \SwFwLess\components\utils\bitmap\bitarray\BitIntArr();

        for ($i = 1; $i <= 10000; ++$i) {
            $bitIntArr->put($i);
        }

        for ($i = 1; $i <= 10000; ++$i) {
            $bitIntArr->del($i);
            $this->assertFalse($bitIntArr->has($i));
        }

        for ($i = 1; $i <= 10000; ++$i) {
            $this->assertFalse($bitIntArr->has($i));
        }

        //Case 2
        $bitIntArr = new \SwFwLess\components\utils\bitmap\bitarray\BitIntArr();

        for ($i = 1; $i <= 10000; ++$i) {
            $bitIntArr->put($i);
            $bitIntArr->del($i);
            $this->assertFalse($bitIntArr->has($i));
        }

        for ($i = 1; $i <= 10000; ++$i) {
            $this->assertFalse($bitIntArr->has($i));
        }

        //Case 3
        $bitIntArr = new \SwFwLess\components\utils\bitmap\bitarray\BitIntArr();

        for ($i = 1; $i <= 10000; $i += 2) {
            $bitIntArr->put($i);
        }

        for ($i = 1; $i <= 10000; $i += 2) {
            $bitIntArr->del($i);
            $this->assertFalse($bitIntArr->has($i));
        }

        for ($i = 1; $i <= 10000; ++$i) {
            $this->assertFalse($bitIntArr->has($i));
        }

        //Case 4
        $bitIntArr = new \SwFwLess\components\utils\bitmap\bitarray\BitIntArr();

        for ($i = 1; $i <= 10000; $i += 2) {
            $bitIntArr->put($i);
            $bitIntArr->del($i);
            $this->assertFalse($bitIntArr->has($i));
        }

        for ($i = 1; $i <= 10000; ++$i) {
            $this->assertFalse($bitIntArr->has($i));
        }
    }

    public function testIterator()
    {
        $bitIntArr = new \SwFwLess\components\utils\bitmap\bitarray\BitIntArr();

        for ($i = 1; $i <= 10000; ++$i) {
            $bitIntArr->put($i);
        }

        $i = 0;
        foreach ($bitIntArr->iterator() as $number) {
            $this->assertEquals(++$i, $number);
        }
    }
}
