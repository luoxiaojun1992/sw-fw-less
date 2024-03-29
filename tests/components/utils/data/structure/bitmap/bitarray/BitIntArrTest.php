<?php

namespace SwFwLessTest\components\utils\data\structure\bitmap\bitarray;

class BitIntArrTest extends \PHPUnit\Framework\TestCase
{
    public function testPut()
    {
        //Case 1
        $bitIntArr = new \SwFwLess\components\utils\data\structure\bitmap\bitarray\BitIntArr();

        for ($i = 1; $i <= 10000; ++$i) {
            $bitIntArr->put($i);
        }

        //Case 2
        $bitIntArr = new \SwFwLess\components\utils\data\structure\bitmap\bitarray\BitIntArr();

        for ($i = 1; $i <= 10000; $i += 2) {
            $bitIntArr->put($i);
        }

        //Case 3
        $bitIntArr = new \SwFwLess\components\utils\data\structure\bitmap\bitarray\BitIntArr();

        for ($i = 1; $i <= 10000; ++$i) {
            $bitIntArr->put($i);
        }

        for ($i = 20001; $i <= 30000; ++$i) {
            $bitIntArr->put($i);
        }

        //Case 4
        $bitIntArr = new \SwFwLess\components\utils\data\structure\bitmap\bitarray\BitIntArr();

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
        $bitIntArr = new \SwFwLess\components\utils\data\structure\bitmap\bitarray\BitIntArr();

        for ($i = 1; $i <= 10000; ++$i) {
            $bitIntArr->put($i);
            $this->assertTrue($bitIntArr->has($i));
        }

        for ($i = 1; $i <= 10000; ++$i) {
            $this->assertTrue($bitIntArr->has($i));
        }

        //Case 2
        $bitIntArr = new \SwFwLess\components\utils\data\structure\bitmap\bitarray\BitIntArr();

        for ($i = 1; $i <= 10000; $i += 2) {
            $bitIntArr->put($i);
            $this->assertTrue($bitIntArr->has($i));
        }

        for ($i = 1; $i <= 10000; $i += 2) {
            $this->assertTrue($bitIntArr->has($i));
        }

        //Case 3
        $bitIntArr = new \SwFwLess\components\utils\data\structure\bitmap\bitarray\BitIntArr();

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
        $bitIntArr = new \SwFwLess\components\utils\data\structure\bitmap\bitarray\BitIntArr();

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
        $bitIntArr = new \SwFwLess\components\utils\data\structure\bitmap\bitarray\BitIntArr();

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
        $bitIntArr = new \SwFwLess\components\utils\data\structure\bitmap\bitarray\BitIntArr();

        for ($i = 1; $i <= 10000; ++$i) {
            $bitIntArr->put($i);
            $bitIntArr->del($i);
            $this->assertFalse($bitIntArr->has($i));
        }

        for ($i = 1; $i <= 10000; ++$i) {
            $this->assertFalse($bitIntArr->has($i));
        }

        //Case 3
        $bitIntArr = new \SwFwLess\components\utils\data\structure\bitmap\bitarray\BitIntArr();

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
        $bitIntArr = new \SwFwLess\components\utils\data\structure\bitmap\bitarray\BitIntArr();

        for ($i = 1; $i <= 10000; $i += 2) {
            $bitIntArr->put($i);
            $bitIntArr->del($i);
            $this->assertFalse($bitIntArr->has($i));
        }

        for ($i = 1; $i <= 10000; ++$i) {
            $this->assertFalse($bitIntArr->has($i));
        }

        //Case 5
        $bitIntArr = new \SwFwLess\components\utils\data\structure\bitmap\bitarray\BitIntArr();

        for ($i = 1; $i <= 10000; ++$i) {
            $bitIntArr->put($i);
        }

        for ($i = 1; $i <= 10000; $i += 2) {
            $bitIntArr->del($i);
            $this->assertFalse($bitIntArr->has($i));
        }

        for ($i = 1; $i <= 10000; ++$i) {
            if (($i % 2) == 0) {
                $this->assertTrue($bitIntArr->has($i));
            } else {
                $this->assertFalse($bitIntArr->has($i));
            }
        }

        //Case 6
        $bitIntArr = new \SwFwLess\components\utils\data\structure\bitmap\bitarray\BitIntArr();

        for ($i = 1; $i <= 10000; ++$i) {
            $bitIntArr->put($i);
        }

        for ($i = 20001; $i <= 30000; ++$i) {
            $bitIntArr->put($i);
        }

        for ($i = 1; $i <= 10000; ++$i) {
            $bitIntArr->del($i);
            $this->assertFalse($bitIntArr->has($i));
        }

        for ($i = 20001; $i <= 30000; ++$i) {
            $bitIntArr->del($i);
            $this->assertFalse($bitIntArr->has($i));
        }

        for ($i = 1; $i <= 10000; ++$i) {
            $this->assertFalse($bitIntArr->has($i));
        }

        for ($i = 20001; $i <= 30000; ++$i) {
            $this->assertFalse($bitIntArr->has($i));
        }

        //Case 7
        $bitIntArr = new \SwFwLess\components\utils\data\structure\bitmap\bitarray\BitIntArr();

        for ($i = 1; $i <= 10000; ++$i) {
            $bitIntArr->put($i);
            $bitIntArr->del($i);
            $this->assertFalse($bitIntArr->has($i));
        }

        for ($i = 20001; $i <= 30000; ++$i) {
            $bitIntArr->put($i);
            $bitIntArr->del($i);
            $this->assertFalse($bitIntArr->has($i));
        }

        for ($i = 1; $i <= 10000; ++$i) {
            $this->assertFalse($bitIntArr->has($i));
        }

        for ($i = 20001; $i <= 30000; ++$i) {
            $this->assertFalse($bitIntArr->has($i));
        }

        //Case 8
        $bitIntArr = new \SwFwLess\components\utils\data\structure\bitmap\bitarray\BitIntArr();

        for ($i = 1; $i <= 10000; $i += 2) {
            $bitIntArr->put($i);
        }

        for ($i = 20001; $i <= 30000; $i += 2) {
            $bitIntArr->put($i);
        }

        for ($i = 1; $i <= 10000; $i += 2) {
            $bitIntArr->del($i);
            $this->assertFalse($bitIntArr->has($i));
        }

        for ($i = 20001; $i <= 30000; $i += 2) {
            $bitIntArr->del($i);
            $this->assertFalse($bitIntArr->has($i));
        }

        for ($i = 1; $i <= 10000; ++$i) {
            $this->assertFalse($bitIntArr->has($i));
        }

        for ($i = 20001; $i <= 30000; ++$i) {
            $this->assertFalse($bitIntArr->has($i));
        }

        //Case 9
        $bitIntArr = new \SwFwLess\components\utils\data\structure\bitmap\bitarray\BitIntArr();

        for ($i = 1; $i <= 10000; $i += 2) {
            $bitIntArr->put($i);
            $bitIntArr->del($i);
            $this->assertFalse($bitIntArr->has($i));
        }

        for ($i = 20001; $i <= 30000; $i += 2) {
            $bitIntArr->put($i);
            $bitIntArr->del($i);
            $this->assertFalse($bitIntArr->has($i));
        }

        for ($i = 1; $i <= 10000; ++$i) {
            $this->assertFalse($bitIntArr->has($i));
        }

        for ($i = 20001; $i <= 30000; ++$i) {
            $this->assertFalse($bitIntArr->has($i));
        }

        //Case 10
        $bitIntArr = new \SwFwLess\components\utils\data\structure\bitmap\bitarray\BitIntArr();

        for ($i = 1; $i <= 10000; ++$i) {
            $bitIntArr->put($i);
        }

        for ($i = 20001; $i <= 30000; ++$i) {
            $bitIntArr->put($i);
        }

        for ($i = 1; $i <= 10000; $i += 2) {
            $bitIntArr->del($i);
            $this->assertFalse($bitIntArr->has($i));
        }

        for ($i = 20001; $i <= 30000; $i += 2) {
            $bitIntArr->del($i);
            $this->assertFalse($bitIntArr->has($i));
        }

        for ($i = 1; $i <= 10000; ++$i) {
            if (($i % 2) == 0) {
                $this->assertTrue($bitIntArr->has($i));
            } else {
                $this->assertFalse($bitIntArr->has($i));
            }
        }

        for ($i = 20001; $i <= 30000; ++$i) {
            if (($i % 2) == 0) {
                $this->assertTrue($bitIntArr->has($i));
            } else {
                $this->assertFalse($bitIntArr->has($i));
            }
        }
    }

    public function testIterator()
    {
        //Case 1
        $bitIntArr = new \SwFwLess\components\utils\data\structure\bitmap\bitarray\BitIntArr();

        for ($i = 1; $i <= 10000; ++$i) {
            $bitIntArr->put($i);
        }

        $i = 0;
        foreach ($bitIntArr->iterator() as $number) {
            $this->assertEquals(++$i, $number);
        }

        //Case 2
        $bitIntArr = new \SwFwLess\components\utils\data\structure\bitmap\bitarray\BitIntArr();

        for ($i = 1; $i <= 10000; $i += 2) {
            $bitIntArr->put($i);
        }

        $i = 1;
        foreach ($bitIntArr->iterator() as $number) {
            $this->assertEquals($i, $number);
            $i += 2;
        }

        //Case 3
        $bitIntArr = new \SwFwLess\components\utils\data\structure\bitmap\bitarray\BitIntArr();

        for ($i = 1; $i <= 10000; ++$i) {
            $bitIntArr->put($i);
        }

        for ($i = 20001; $i <= 30000; ++$i) {
            $bitIntArr->put($i);
        }

        $i = 0;
        foreach ($bitIntArr->iterator() as $number) {
            $this->assertEquals(++$i, $number);
            if ($i === 10000) {
                $i = 20000;
            }
        }

        //Case 4
        $bitIntArr = new \SwFwLess\components\utils\data\structure\bitmap\bitarray\BitIntArr();

        for ($i = 1; $i <= 10000; $i += 2) {
            $bitIntArr->put($i);
        }

        for ($i = 20001; $i <= 30000; $i += 2) {
            $bitIntArr->put($i);
        }

        $i = 1;
        foreach ($bitIntArr->iterator() as $number) {
            $this->assertEquals($i, $number);
            $i += 2;
            if ($i > 10000 && $i < 20001) {
                $i = 20001;
            }
        }
    }

    public function testCreateFromSlots()
    {
        //Case 1
        $bitIntArr = new \SwFwLess\components\utils\data\structure\bitmap\bitarray\BitIntArr();

        for ($i = 1; $i <= 10000; ++$i) {
            $bitIntArr->put($i);
            $this->assertTrue($bitIntArr->has($i));
        }

        for ($i = 1; $i <= 10000; ++$i) {
            $this->assertTrue($bitIntArr->has($i));
        }

        $bitIntArr = \SwFwLess\components\utils\data\structure\bitmap\bitarray\BitIntArr::createFromSlots($bitIntArr->getSlots());

        for ($i = 1; $i <= 10000; ++$i) {
            $this->assertTrue($bitIntArr->has($i));
        }

        //Case 2
        $bitIntArr = new \SwFwLess\components\utils\data\structure\bitmap\bitarray\BitIntArr();

        for ($i = 1; $i <= 10000; $i += 2) {
            $bitIntArr->put($i);
            $this->assertTrue($bitIntArr->has($i));
        }

        for ($i = 1; $i <= 10000; $i += 2) {
            $this->assertTrue($bitIntArr->has($i));
        }

        $bitIntArr = \SwFwLess\components\utils\data\structure\bitmap\bitarray\BitIntArr::createFromSlots($bitIntArr->getSlots());

        for ($i = 1; $i <= 10000; $i += 2) {
            $this->assertTrue($bitIntArr->has($i));
        }

        //Case 3
        $bitIntArr = new \SwFwLess\components\utils\data\structure\bitmap\bitarray\BitIntArr();

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

        $bitIntArr = \SwFwLess\components\utils\data\structure\bitmap\bitarray\BitIntArr::createFromSlots($bitIntArr->getSlots());

        for ($i = 1; $i <= 10000; ++$i) {
            $this->assertTrue($bitIntArr->has($i));
        }

        for ($i = 20001; $i <= 30000; ++$i) {
            $this->assertTrue($bitIntArr->has($i));
        }

        //Case 4
        $bitIntArr = new \SwFwLess\components\utils\data\structure\bitmap\bitarray\BitIntArr();

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

        $bitIntArr = \SwFwLess\components\utils\data\structure\bitmap\bitarray\BitIntArr::createFromSlots($bitIntArr->getSlots());

        for ($i = 1; $i <= 10000; $i += 2) {
            $this->assertTrue($bitIntArr->has($i));
        }

        for ($i = 20001; $i <= 30000; $i += 2) {
            $this->assertTrue($bitIntArr->has($i));
        }

        //Case 5
        $bitIntArr = new \SwFwLess\components\utils\data\structure\bitmap\bitarray\BitIntArr();

        for ($i = 1; $i <= 10000; ++$i) {
            $bitIntArr->put($i);
        }

        for ($i = 1; $i <= 10000; $i += 2) {
            $bitIntArr->del($i);
            $this->assertFalse($bitIntArr->has($i));
        }

        for ($i = 1; $i <= 10000; ++$i) {
            if (($i % 2) == 0) {
                $this->assertTrue($bitIntArr->has($i));
            } else {
                $this->assertFalse($bitIntArr->has($i));
            }
        }

        $bitIntArr = \SwFwLess\components\utils\data\structure\bitmap\bitarray\BitIntArr::createFromSlots($bitIntArr->getSlots());

        for ($i = 1; $i <= 10000; ++$i) {
            if (($i % 2) == 0) {
                $this->assertTrue($bitIntArr->has($i));
            } else {
                $this->assertFalse($bitIntArr->has($i));
            }
        }

        //Case 6
        $bitIntArr = new \SwFwLess\components\utils\data\structure\bitmap\bitarray\BitIntArr();

        for ($i = 1; $i <= 10000; ++$i) {
            $bitIntArr->put($i);
        }

        for ($i = 20001; $i <= 30000; ++$i) {
            $bitIntArr->put($i);
        }

        for ($i = 1; $i <= 10000; $i += 2) {
            $bitIntArr->del($i);
            $this->assertFalse($bitIntArr->has($i));
        }

        for ($i = 20001; $i <= 30000; $i += 2) {
            $bitIntArr->del($i);
            $this->assertFalse($bitIntArr->has($i));
        }

        for ($i = 1; $i <= 10000; ++$i) {
            if (($i % 2) == 0) {
                $this->assertTrue($bitIntArr->has($i));
            } else {
                $this->assertFalse($bitIntArr->has($i));
            }
        }

        for ($i = 20001; $i <= 30000; ++$i) {
            if (($i % 2) == 0) {
                $this->assertTrue($bitIntArr->has($i));
            } else {
                $this->assertFalse($bitIntArr->has($i));
            }
        }

        $bitIntArr = \SwFwLess\components\utils\data\structure\bitmap\bitarray\BitIntArr::createFromSlots($bitIntArr->getSlots());

        for ($i = 1; $i <= 10000; ++$i) {
            if (($i % 2) == 0) {
                $this->assertTrue($bitIntArr->has($i));
            } else {
                $this->assertFalse($bitIntArr->has($i));
            }
        }

        for ($i = 20001; $i <= 30000; ++$i) {
            if (($i % 2) == 0) {
                $this->assertTrue($bitIntArr->has($i));
            } else {
                $this->assertFalse($bitIntArr->has($i));
            }
        }

        //Case 7
        $bitIntArr = new \SwFwLess\components\utils\data\structure\bitmap\bitarray\BitIntArr();

        for ($i = 1; $i <= 10000; ++$i) {
            $bitIntArr->put($i);
        }

        $i = 0;
        foreach ($bitIntArr->iterator() as $number) {
            $this->assertEquals(++$i, $number);
        }

        $bitIntArr = \SwFwLess\components\utils\data\structure\bitmap\bitarray\BitIntArr::createFromSlots($bitIntArr->getSlots());

        $i = 0;
        foreach ($bitIntArr->iterator() as $number) {
            $this->assertEquals(++$i, $number);
        }

        //Case 8
        $bitIntArr = new \SwFwLess\components\utils\data\structure\bitmap\bitarray\BitIntArr();

        for ($i = 1; $i <= 10000; $i += 2) {
            $bitIntArr->put($i);
        }

        $i = 1;
        foreach ($bitIntArr->iterator() as $number) {
            $this->assertEquals($i, $number);
            $i += 2;
        }

        $bitIntArr = \SwFwLess\components\utils\data\structure\bitmap\bitarray\BitIntArr::createFromSlots($bitIntArr->getSlots());

        $i = 1;
        foreach ($bitIntArr->iterator() as $number) {
            $this->assertEquals($i, $number);
            $i += 2;
        }

        //Case 9
        $bitIntArr = new \SwFwLess\components\utils\data\structure\bitmap\bitarray\BitIntArr();

        for ($i = 1; $i <= 10000; ++$i) {
            $bitIntArr->put($i);
        }

        for ($i = 20001; $i <= 30000; ++$i) {
            $bitIntArr->put($i);
        }

        $i = 0;
        foreach ($bitIntArr->iterator() as $number) {
            $this->assertEquals(++$i, $number);
            if ($i === 10000) {
                $i = 20000;
            }
        }

        $bitIntArr = \SwFwLess\components\utils\data\structure\bitmap\bitarray\BitIntArr::createFromSlots($bitIntArr->getSlots());

        $i = 0;
        foreach ($bitIntArr->iterator() as $number) {
            $this->assertEquals(++$i, $number);
            if ($i === 10000) {
                $i = 20000;
            }
        }

        //Case 10
        $bitIntArr = new \SwFwLess\components\utils\data\structure\bitmap\bitarray\BitIntArr();

        for ($i = 1; $i <= 10000; $i += 2) {
            $bitIntArr->put($i);
        }

        for ($i = 20001; $i <= 30000; $i += 2) {
            $bitIntArr->put($i);
        }

        $i = 1;
        foreach ($bitIntArr->iterator() as $number) {
            $this->assertEquals($i, $number);
            $i += 2;
            if ($i > 10000 && $i < 20001) {
                $i = 20001;
            }
        }

        $bitIntArr = \SwFwLess\components\utils\data\structure\bitmap\bitarray\BitIntArr::createFromSlots($bitIntArr->getSlots());

        $i = 1;
        foreach ($bitIntArr->iterator() as $number) {
            $this->assertEquals($i, $number);
            $i += 2;
            if ($i > 10000 && $i < 20001) {
                $i = 20001;
            }
        }
    }
}
