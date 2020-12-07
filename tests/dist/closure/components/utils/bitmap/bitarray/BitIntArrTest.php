<?php

class BitIntArrClosureTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @throws Exception
     */
    public function testPut()
    {
        //Case 1
        $bitIntArr = \Lxj\ClosurePHP\Sugars\Object\newObj(
            'SwFwLess\components\utils\bitmap\bitarray\BitIntArr'
        );

        for ($i = 1; $i <= 10000; ++$i) {
            \Lxj\ClosurePHP\Sugars\Object\call(
                $bitIntArr,
                'put',
                \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                [$i]
            );
        }

        //Case 2
        $bitIntArr = \Lxj\ClosurePHP\Sugars\Object\newObj(
            'SwFwLess\components\utils\bitmap\bitarray\BitIntArr'
        );

        for ($i = 1; $i <= 10000; $i += 2) {
            \Lxj\ClosurePHP\Sugars\Object\call(
                $bitIntArr,
                'put',
                \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                [$i]
            );
        }

        //Case 3
        $bitIntArr = \Lxj\ClosurePHP\Sugars\Object\newObj(
            'SwFwLess\components\utils\bitmap\bitarray\BitIntArr'
        );

        for ($i = 1; $i <= 10000; ++$i) {
            \Lxj\ClosurePHP\Sugars\Object\call(
                $bitIntArr,
                'put',
                \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                [$i]
            );
        }

        for ($i = 20001; $i <= 30000; ++$i) {
            \Lxj\ClosurePHP\Sugars\Object\call(
                $bitIntArr,
                'put',
                \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                [$i]
            );
        }

        //Case 4
        $bitIntArr = \Lxj\ClosurePHP\Sugars\Object\newObj(
            'SwFwLess\components\utils\bitmap\bitarray\BitIntArr'
        );

        for ($i = 1; $i <= 10000; $i += 2) {
            \Lxj\ClosurePHP\Sugars\Object\call(
                $bitIntArr,
                'put',
                \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                [$i]
            );
        }

        for ($i = 20001; $i <= 30000; $i += 2) {
            \Lxj\ClosurePHP\Sugars\Object\call(
                $bitIntArr,
                'put',
                \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                [$i]
            );
        }

        $this->assertTrue(true);
    }

    /**
     * @throws Exception
     */
    public function testHas()
    {
        //Case 1
        $bitIntArr = \Lxj\ClosurePHP\Sugars\Object\newObj(
            'SwFwLess\components\utils\bitmap\bitarray\BitIntArr'
        );

        for ($i = 1; $i <= 10000; ++$i) {
            \Lxj\ClosurePHP\Sugars\Object\call(
                $bitIntArr,
                'put',
                \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                [$i]
            );
            $this->assertTrue(
                \Lxj\ClosurePHP\Sugars\Object\call(
                    $bitIntArr,
                    'has',
                    \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                    [$i]
                )
            );
        }

        for ($i = 1; $i <= 10000; ++$i) {
            $this->assertTrue(
                \Lxj\ClosurePHP\Sugars\Object\call(
                    $bitIntArr,
                    'has',
                    \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                    [$i]
                )
            );
        }

        //Case 2
        $bitIntArr = \Lxj\ClosurePHP\Sugars\Object\newObj(
            'SwFwLess\components\utils\bitmap\bitarray\BitIntArr'
        );

        for ($i = 1; $i <= 10000; $i += 2) {
            \Lxj\ClosurePHP\Sugars\Object\call(
                $bitIntArr,
                'put',
                \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                [$i]
            );
            $this->assertTrue(
                \Lxj\ClosurePHP\Sugars\Object\call(
                    $bitIntArr,
                    'has',
                    \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                    [$i]
                )
            );
        }

        for ($i = 1; $i <= 10000; $i += 2) {
            $this->assertTrue(
                \Lxj\ClosurePHP\Sugars\Object\call(
                    $bitIntArr,
                    'has',
                    \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                    [$i]
                )
            );
        }

        //Case 3
        $bitIntArr = \Lxj\ClosurePHP\Sugars\Object\newObj(
            'SwFwLess\components\utils\bitmap\bitarray\BitIntArr'
        );

        for ($i = 1; $i <= 10000; ++$i) {
            \Lxj\ClosurePHP\Sugars\Object\call(
                $bitIntArr,
                'put',
                \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                [$i]
            );
            $this->assertTrue(
                \Lxj\ClosurePHP\Sugars\Object\call(
                    $bitIntArr,
                    'has',
                    \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                    [$i]
                )
            );
        }

        for ($i = 1; $i <= 10000; ++$i) {
            $this->assertTrue(
                \Lxj\ClosurePHP\Sugars\Object\call(
                    $bitIntArr,
                    'has',
                    \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                    [$i]
                )
            );
        }

        for ($i = 20001; $i <= 30000; ++$i) {
            \Lxj\ClosurePHP\Sugars\Object\call(
                $bitIntArr,
                'put',
                \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                [$i]
            );
            $this->assertTrue(
                \Lxj\ClosurePHP\Sugars\Object\call(
                    $bitIntArr,
                    'has',
                    \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                    [$i]
                )
            );
        }

        for ($i = 20001; $i <= 30000; ++$i) {
            $this->assertTrue(
                \Lxj\ClosurePHP\Sugars\Object\call(
                    $bitIntArr,
                    'has',
                    \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                    [$i]
                )
            );
        }

        //Case 4
        $bitIntArr = \Lxj\ClosurePHP\Sugars\Object\newObj(
            'SwFwLess\components\utils\bitmap\bitarray\BitIntArr'
        );

        for ($i = 1; $i <= 10000; $i += 2) {
            \Lxj\ClosurePHP\Sugars\Object\call(
                $bitIntArr,
                'put',
                \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                [$i]
            );
            $this->assertTrue(
                \Lxj\ClosurePHP\Sugars\Object\call(
                    $bitIntArr,
                    'has',
                    \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                    [$i]
                )
            );
        }

        for ($i = 1; $i <= 10000; $i += 2) {
            $this->assertTrue(
                \Lxj\ClosurePHP\Sugars\Object\call(
                    $bitIntArr,
                    'has',
                    \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                    [$i]
                )
            );
        }

        for ($i = 20001; $i <= 30000; $i += 2) {
            \Lxj\ClosurePHP\Sugars\Object\call(
                $bitIntArr,
                'put',
                \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                [$i]
            );
            $this->assertTrue(
                \Lxj\ClosurePHP\Sugars\Object\call(
                    $bitIntArr,
                    'has',
                    \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                    [$i]
                )
            );
        }

        for ($i = 20001; $i <= 30000; $i += 2) {
            $this->assertTrue(
                \Lxj\ClosurePHP\Sugars\Object\call(
                    $bitIntArr,
                    'has',
                    \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                    [$i]
                )
            );
        }
    }

    /**
     * @throws Exception
     */
    public function testDelete()
    {
        //Case 1
        $bitIntArr = \Lxj\ClosurePHP\Sugars\Object\newObj(
            'SwFwLess\components\utils\bitmap\bitarray\BitIntArr'
        );

        for ($i = 1; $i <= 10000; ++$i) {
            \Lxj\ClosurePHP\Sugars\Object\call(
                $bitIntArr,
                'put',
                \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                [$i]
            );
        }

        for ($i = 1; $i <= 10000; ++$i) {
            \Lxj\ClosurePHP\Sugars\Object\call(
                $bitIntArr,
                'del',
                \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                [$i]
            );
            $this->assertFalse(
                \Lxj\ClosurePHP\Sugars\Object\call(
                    $bitIntArr,
                    'has',
                    \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                    [$i]
                )
            );
        }

        for ($i = 1; $i <= 10000; ++$i) {
            $this->assertFalse(
                \Lxj\ClosurePHP\Sugars\Object\call(
                    $bitIntArr,
                    'has',
                    \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                    [$i]
                )
            );
        }

        //Case 2
        $bitIntArr = \Lxj\ClosurePHP\Sugars\Object\newObj(
            'SwFwLess\components\utils\bitmap\bitarray\BitIntArr'
        );

        for ($i = 1; $i <= 10000; ++$i) {
            \Lxj\ClosurePHP\Sugars\Object\call(
                $bitIntArr,
                'put',
                \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                [$i]
            );
            \Lxj\ClosurePHP\Sugars\Object\call(
                $bitIntArr,
                'del',
                \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                [$i]
            );
            $this->assertFalse(
                \Lxj\ClosurePHP\Sugars\Object\call(
                    $bitIntArr,
                    'has',
                    \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                    [$i]
                )
            );
        }

        for ($i = 1; $i <= 10000; ++$i) {
            $this->assertFalse(
                \Lxj\ClosurePHP\Sugars\Object\call(
                    $bitIntArr,
                    'has',
                    \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                    [$i]
                )
            );
        }

        //Case 3
        $bitIntArr = \Lxj\ClosurePHP\Sugars\Object\newObj(
            'SwFwLess\components\utils\bitmap\bitarray\BitIntArr'
        );

        for ($i = 1; $i <= 10000; $i += 2) {
            \Lxj\ClosurePHP\Sugars\Object\call(
                $bitIntArr,
                'put',
                \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                [$i]
            );
        }

        for ($i = 1; $i <= 10000; $i += 2) {
            \Lxj\ClosurePHP\Sugars\Object\call(
                $bitIntArr,
                'del',
                \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                [$i]
            );
            $this->assertFalse(
                \Lxj\ClosurePHP\Sugars\Object\call(
                    $bitIntArr,
                    'has',
                    \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                    [$i]
                )
            );
        }

        for ($i = 1; $i <= 10000; ++$i) {
            $this->assertFalse(
                \Lxj\ClosurePHP\Sugars\Object\call(
                    $bitIntArr,
                    'has',
                    \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                    [$i]
                )
            );
        }

        //Case 4
        $bitIntArr = \Lxj\ClosurePHP\Sugars\Object\newObj(
            'SwFwLess\components\utils\bitmap\bitarray\BitIntArr'
        );

        for ($i = 1; $i <= 10000; $i += 2) {
            \Lxj\ClosurePHP\Sugars\Object\call(
                $bitIntArr,
                'put',
                \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                [$i]
            );
            \Lxj\ClosurePHP\Sugars\Object\call(
                $bitIntArr,
                'del',
                \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                [$i]
            );
            $this->assertFalse(
                \Lxj\ClosurePHP\Sugars\Object\call(
                    $bitIntArr,
                    'has',
                    \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                    [$i]
                )
            );
        }

        for ($i = 1; $i <= 10000; ++$i) {
            $this->assertFalse(
                \Lxj\ClosurePHP\Sugars\Object\call(
                    $bitIntArr,
                    'has',
                    \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                    [$i]
                )
            );
        }

        //Case 5
        $bitIntArr = \Lxj\ClosurePHP\Sugars\Object\newObj(
            'SwFwLess\components\utils\bitmap\bitarray\BitIntArr'
        );

        for ($i = 1; $i <= 10000; ++$i) {
            \Lxj\ClosurePHP\Sugars\Object\call(
                $bitIntArr,
                'put',
                \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                [$i]
            );
        }

        for ($i = 1; $i <= 10000; $i += 2) {
            \Lxj\ClosurePHP\Sugars\Object\call(
                $bitIntArr,
                'del',
                \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                [$i]
            );
            $this->assertFalse(
                \Lxj\ClosurePHP\Sugars\Object\call(
                    $bitIntArr,
                    'has',
                    \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                    [$i]
                )
            );
        }

        for ($i = 1; $i <= 10000; ++$i) {
            if (($i % 2) == 0) {
                $this->assertTrue(
                    \Lxj\ClosurePHP\Sugars\Object\call(
                        $bitIntArr,
                        'has',
                        \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                        [$i]
                    )
                );
            } else {
                $this->assertFalse(
                    \Lxj\ClosurePHP\Sugars\Object\call(
                        $bitIntArr,
                        'has',
                        \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                        [$i]
                    )
                );
            }
        }

        //Case 6
        $bitIntArr = \Lxj\ClosurePHP\Sugars\Object\newObj(
            'SwFwLess\components\utils\bitmap\bitarray\BitIntArr'
        );

        for ($i = 1; $i <= 10000; ++$i) {
            \Lxj\ClosurePHP\Sugars\Object\call(
                $bitIntArr,
                'put',
                \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                [$i]
            );
        }

        for ($i = 20001; $i <= 30000; ++$i) {
            \Lxj\ClosurePHP\Sugars\Object\call(
                $bitIntArr,
                'put',
                \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                [$i]
            );
        }

        for ($i = 1; $i <= 10000; ++$i) {
            \Lxj\ClosurePHP\Sugars\Object\call(
                $bitIntArr,
                'del',
                \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                [$i]
            );
            $this->assertFalse(
                \Lxj\ClosurePHP\Sugars\Object\call(
                    $bitIntArr,
                    'has',
                    \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                    [$i]
                )
            );
        }

        for ($i = 20001; $i <= 30000; ++$i) {
            \Lxj\ClosurePHP\Sugars\Object\call(
                $bitIntArr,
                'del',
                \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                [$i]
            );
            $this->assertFalse(
                \Lxj\ClosurePHP\Sugars\Object\call(
                    $bitIntArr,
                    'has',
                    \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                    [$i]
                )
            );
        }

        for ($i = 1; $i <= 10000; ++$i) {
            $this->assertFalse(
                \Lxj\ClosurePHP\Sugars\Object\call(
                    $bitIntArr,
                    'has',
                    \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                    [$i]
                )
            );
        }

        for ($i = 20001; $i <= 30000; ++$i) {
            $this->assertFalse(
                \Lxj\ClosurePHP\Sugars\Object\call(
                    $bitIntArr,
                    'has',
                    \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                    [$i]
                )
            );
        }

        //Case 7
        $bitIntArr = \Lxj\ClosurePHP\Sugars\Object\newObj(
            'SwFwLess\components\utils\bitmap\bitarray\BitIntArr'
        );

        for ($i = 1; $i <= 10000; ++$i) {
            \Lxj\ClosurePHP\Sugars\Object\call(
                $bitIntArr,
                'put',
                \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                [$i]
            );
            \Lxj\ClosurePHP\Sugars\Object\call(
                $bitIntArr,
                'del',
                \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                [$i]
            );
            $this->assertFalse(
                \Lxj\ClosurePHP\Sugars\Object\call(
                    $bitIntArr,
                    'has',
                    \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                    [$i]
                )
            );
        }

        for ($i = 20001; $i <= 30000; ++$i) {
            \Lxj\ClosurePHP\Sugars\Object\call(
                $bitIntArr,
                'put',
                \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                [$i]
            );
            \Lxj\ClosurePHP\Sugars\Object\call(
                $bitIntArr,
                'del',
                \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                [$i]
            );
            $this->assertFalse(
                \Lxj\ClosurePHP\Sugars\Object\call(
                    $bitIntArr,
                    'has',
                    \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                    [$i]
                )
            );
        }

        for ($i = 1; $i <= 10000; ++$i) {
            $this->assertFalse(
                \Lxj\ClosurePHP\Sugars\Object\call(
                    $bitIntArr,
                    'has',
                    \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                    [$i]
                )
            );
        }

        for ($i = 20001; $i <= 30000; ++$i) {
            $this->assertFalse(
                \Lxj\ClosurePHP\Sugars\Object\call(
                    $bitIntArr,
                    'has',
                    \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                    [$i]
                )
            );
        }

        //Case 8
        $bitIntArr = \Lxj\ClosurePHP\Sugars\Object\newObj(
            'SwFwLess\components\utils\bitmap\bitarray\BitIntArr'
        );

        for ($i = 1; $i <= 10000; $i += 2) {
            \Lxj\ClosurePHP\Sugars\Object\call(
                $bitIntArr,
                'put',
                \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                [$i]
            );
        }

        for ($i = 20001; $i <= 30000; $i += 2) {
            \Lxj\ClosurePHP\Sugars\Object\call(
                $bitIntArr,
                'put',
                \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                [$i]
            );
        }

        for ($i = 1; $i <= 10000; $i += 2) {
            \Lxj\ClosurePHP\Sugars\Object\call(
                $bitIntArr,
                'del',
                \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                [$i]
            );
            $this->assertFalse(
                \Lxj\ClosurePHP\Sugars\Object\call(
                    $bitIntArr,
                    'has',
                    \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                    [$i]
                )
            );
        }

        for ($i = 20001; $i <= 30000; $i += 2) {
            \Lxj\ClosurePHP\Sugars\Object\call(
                $bitIntArr,
                'del',
                \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                [$i]
            );
            $this->assertFalse(
                \Lxj\ClosurePHP\Sugars\Object\call(
                    $bitIntArr,
                    'has',
                    \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                    [$i]
                )
            );
        }

        for ($i = 1; $i <= 10000; ++$i) {
            $this->assertFalse(
                \Lxj\ClosurePHP\Sugars\Object\call(
                    $bitIntArr,
                    'has',
                    \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                    [$i]
                )
            );
        }

        for ($i = 20001; $i <= 30000; ++$i) {
            $this->assertFalse(
                \Lxj\ClosurePHP\Sugars\Object\call(
                    $bitIntArr,
                    'has',
                    \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                    [$i]
                )
            );
        }

        //Case 9
        $bitIntArr = \Lxj\ClosurePHP\Sugars\Object\newObj(
            'SwFwLess\components\utils\bitmap\bitarray\BitIntArr'
        );

        for ($i = 1; $i <= 10000; $i += 2) {
            \Lxj\ClosurePHP\Sugars\Object\call(
                $bitIntArr,
                'put',
                \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                [$i]
            );
            \Lxj\ClosurePHP\Sugars\Object\call(
                $bitIntArr,
                'del',
                \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                [$i]
            );
            $this->assertFalse(
                \Lxj\ClosurePHP\Sugars\Object\call(
                    $bitIntArr,
                    'has',
                    \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                    [$i]
                )
            );
        }

        for ($i = 20001; $i <= 30000; $i += 2) {
            \Lxj\ClosurePHP\Sugars\Object\call(
                $bitIntArr,
                'put',
                \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                [$i]
            );
            \Lxj\ClosurePHP\Sugars\Object\call(
                $bitIntArr,
                'del',
                \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                [$i]
            );
            $this->assertFalse(
                \Lxj\ClosurePHP\Sugars\Object\call(
                    $bitIntArr,
                    'has',
                    \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                    [$i]
                )
            );
        }

        for ($i = 1; $i <= 10000; ++$i) {
            $this->assertFalse(
                \Lxj\ClosurePHP\Sugars\Object\call(
                    $bitIntArr,
                    'has',
                    \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                    [$i]
                )
            );
        }

        for ($i = 20001; $i <= 30000; ++$i) {
            $this->assertFalse(
                \Lxj\ClosurePHP\Sugars\Object\call(
                    $bitIntArr,
                    'has',
                    \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                    [$i]
                )
            );
        }

        //Case 10
        $bitIntArr = \Lxj\ClosurePHP\Sugars\Object\newObj(
            'SwFwLess\components\utils\bitmap\bitarray\BitIntArr'
        );

        for ($i = 1; $i <= 10000; ++$i) {
            \Lxj\ClosurePHP\Sugars\Object\call(
                $bitIntArr,
                'put',
                \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                [$i]
            );
        }

        for ($i = 20001; $i <= 30000; ++$i) {
            \Lxj\ClosurePHP\Sugars\Object\call(
                $bitIntArr,
                'put',
                \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                [$i]
            );
        }

        for ($i = 1; $i <= 10000; $i += 2) {
            \Lxj\ClosurePHP\Sugars\Object\call(
                $bitIntArr,
                'del',
                \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                [$i]
            );
            $this->assertFalse(
                \Lxj\ClosurePHP\Sugars\Object\call(
                    $bitIntArr,
                    'has',
                    \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                    [$i]
                )
            );
        }

        for ($i = 20001; $i <= 30000; $i += 2) {
            \Lxj\ClosurePHP\Sugars\Object\call(
                $bitIntArr,
                'del',
                \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                [$i]
            );
            $this->assertFalse(
                \Lxj\ClosurePHP\Sugars\Object\call(
                    $bitIntArr,
                    'has',
                    \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                    [$i]
                )
            );
        }

        for ($i = 1; $i <= 10000; ++$i) {
            if (($i % 2) == 0) {
                $this->assertTrue(
                    \Lxj\ClosurePHP\Sugars\Object\call(
                        $bitIntArr,
                        'has',
                        \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                        [$i]
                    )
                );
            } else {
                $this->assertFalse(
                    \Lxj\ClosurePHP\Sugars\Object\call(
                        $bitIntArr,
                        'has',
                        \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                        [$i]
                    )
                );
            }
        }

        for ($i = 20001; $i <= 30000; ++$i) {
            if (($i % 2) == 0) {
                $this->assertTrue(
                    \Lxj\ClosurePHP\Sugars\Object\call(
                        $bitIntArr,
                        'has',
                        \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                        [$i]
                    )
                );
            } else {
                $this->assertFalse(
                    \Lxj\ClosurePHP\Sugars\Object\call(
                        $bitIntArr,
                        'has',
                        \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                        [$i]
                    )
                );
            }
        }
    }

    /**
     * @throws Exception
     */
    public function testIterator()
    {
        //Case 1
        $bitIntArr = \Lxj\ClosurePHP\Sugars\Object\newObj(
            'SwFwLess\components\utils\bitmap\bitarray\BitIntArr'
        );

        for ($i = 1; $i <= 10000; ++$i) {
            \Lxj\ClosurePHP\Sugars\Object\call(
                $bitIntArr,
                'put',
                \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                [$i]
            );
        }

        $iterator = \Lxj\ClosurePHP\Sugars\Object\call(
            $bitIntArr,
            'iterator',
            \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
            []
        );
        $i = 0;
        foreach ($iterator as $number) {
            $this->assertEquals(++$i, $number);
        }

        //Case 2
        $bitIntArr = \Lxj\ClosurePHP\Sugars\Object\newObj(
            'SwFwLess\components\utils\bitmap\bitarray\BitIntArr'
        );

        for ($i = 1; $i <= 10000; $i += 2) {
            \Lxj\ClosurePHP\Sugars\Object\call(
                $bitIntArr,
                'put',
                \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                [$i]
            );
        }

        $iterator = \Lxj\ClosurePHP\Sugars\Object\call(
            $bitIntArr,
            'iterator',
            \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
            []
        );
        $i = 1;
        foreach ($iterator as $number) {
            $this->assertEquals($i, $number);
            $i += 2;
        }

        //Case 3
        $bitIntArr = \Lxj\ClosurePHP\Sugars\Object\newObj(
            'SwFwLess\components\utils\bitmap\bitarray\BitIntArr'
        );

        for ($i = 1; $i <= 10000; ++$i) {
            \Lxj\ClosurePHP\Sugars\Object\call(
                $bitIntArr,
                'put',
                \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                [$i]
            );
        }

        for ($i = 20001; $i <= 30000; ++$i) {
            \Lxj\ClosurePHP\Sugars\Object\call(
                $bitIntArr,
                'put',
                \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                [$i]
            );
        }

        $iterator = \Lxj\ClosurePHP\Sugars\Object\call(
            $bitIntArr,
            'iterator',
            \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
            []
        );
        $i = 0;
        foreach ($iterator as $number) {
            $this->assertEquals(++$i, $number);
            if ($i === 10000) {
                $i = 20000;
            }
        }

        //Case 4
        $bitIntArr = \Lxj\ClosurePHP\Sugars\Object\newObj(
            'SwFwLess\components\utils\bitmap\bitarray\BitIntArr'
        );

        for ($i = 1; $i <= 10000; $i += 2) {
            \Lxj\ClosurePHP\Sugars\Object\call(
                $bitIntArr,
                'put',
                \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                [$i]
            );
        }

        for ($i = 20001; $i <= 30000; $i += 2) {
            \Lxj\ClosurePHP\Sugars\Object\call(
                $bitIntArr,
                'put',
                \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                [$i]
            );
        }

        $iterator = \Lxj\ClosurePHP\Sugars\Object\call(
            $bitIntArr,
            'iterator',
            \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
            []
        );
        $i = 1;
        foreach ($iterator as $number) {
            $this->assertEquals($i, $number);
            $i += 2;
            if ($i > 10000 && $i < 20001) {
                $i = 20001;
            }
        }
    }

    /**
     * @throws Exception
     */
    public function testCreateFromSlots()
    {
        //Case 1
        $bitIntArr = \Lxj\ClosurePHP\Sugars\Object\newObj(
            'SwFwLess\components\utils\bitmap\bitarray\BitIntArr'
        );

        for ($i = 1; $i <= 10000; ++$i) {
            \Lxj\ClosurePHP\Sugars\Object\call(
                $bitIntArr,
                'put',
                \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                [$i]
            );
            $this->assertTrue(
                \Lxj\ClosurePHP\Sugars\Object\call(
                    $bitIntArr,
                    'has',
                    \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                    [$i]
                )
            );
        }

        for ($i = 1; $i <= 10000; ++$i) {
            $this->assertTrue(
                \Lxj\ClosurePHP\Sugars\Object\call(
                    $bitIntArr,
                    'has',
                    \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                    [$i]
                )
            );
        }

        $slots = \Lxj\ClosurePHP\Sugars\Object\call(
            $bitIntArr,
            'getSlots',
            \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
            []
        );
        $bitIntArr = \Lxj\ClosurePHP\Sugars\Object\callStatic(
            'SwFwLess\components\utils\bitmap\bitarray\BitIntArr',
            'createFromSlots',
            \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
            [$slots]
        );

        for ($i = 1; $i <= 10000; ++$i) {
            $this->assertTrue(
                \Lxj\ClosurePHP\Sugars\Object\call(
                    $bitIntArr,
                    'has',
                    \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                    [$i]
                )
            );
        }

        //Case 2
        $bitIntArr = \Lxj\ClosurePHP\Sugars\Object\newObj(
            'SwFwLess\components\utils\bitmap\bitarray\BitIntArr'
        );

        for ($i = 1; $i <= 10000; $i += 2) {
            \Lxj\ClosurePHP\Sugars\Object\call(
                $bitIntArr,
                'put',
                \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                [$i]
            );
            $this->assertTrue(
                \Lxj\ClosurePHP\Sugars\Object\call(
                    $bitIntArr,
                    'has',
                    \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                    [$i]
                )
            );
        }

        for ($i = 1; $i <= 10000; $i += 2) {
            $this->assertTrue(
                \Lxj\ClosurePHP\Sugars\Object\call(
                    $bitIntArr,
                    'has',
                    \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                    [$i]
                )
            );
        }

        $slots = \Lxj\ClosurePHP\Sugars\Object\call(
            $bitIntArr,
            'getSlots',
            \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
            []
        );
        $bitIntArr = \Lxj\ClosurePHP\Sugars\Object\callStatic(
            'SwFwLess\components\utils\bitmap\bitarray\BitIntArr',
            'createFromSlots',
            \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
            [$slots]
        );

        for ($i = 1; $i <= 10000; $i += 2) {
            $this->assertTrue(
                \Lxj\ClosurePHP\Sugars\Object\call(
                    $bitIntArr,
                    'has',
                    \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                    [$i]
                )
            );
        }

        //Case 3
        $bitIntArr = \Lxj\ClosurePHP\Sugars\Object\newObj(
            'SwFwLess\components\utils\bitmap\bitarray\BitIntArr'
        );

        for ($i = 1; $i <= 10000; ++$i) {
            \Lxj\ClosurePHP\Sugars\Object\call(
                $bitIntArr,
                'put',
                \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                [$i]
            );
            $this->assertTrue(
                \Lxj\ClosurePHP\Sugars\Object\call(
                    $bitIntArr,
                    'has',
                    \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                    [$i]
                )
            );
        }

        for ($i = 1; $i <= 10000; ++$i) {
            $this->assertTrue(
                \Lxj\ClosurePHP\Sugars\Object\call(
                    $bitIntArr,
                    'has',
                    \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                    [$i]
                )
            );
        }

        for ($i = 20001; $i <= 30000; ++$i) {
            \Lxj\ClosurePHP\Sugars\Object\call(
                $bitIntArr,
                'put',
                \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                [$i]
            );
            $this->assertTrue(
                \Lxj\ClosurePHP\Sugars\Object\call(
                    $bitIntArr,
                    'has',
                    \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                    [$i]
                )
            );
        }

        for ($i = 20001; $i <= 30000; ++$i) {
            $this->assertTrue(
                \Lxj\ClosurePHP\Sugars\Object\call(
                    $bitIntArr,
                    'has',
                    \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                    [$i]
                )
            );
        }

        $slots = \Lxj\ClosurePHP\Sugars\Object\call(
            $bitIntArr,
            'getSlots',
            \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
            []
        );
        $bitIntArr = \Lxj\ClosurePHP\Sugars\Object\callStatic(
            'SwFwLess\components\utils\bitmap\bitarray\BitIntArr',
            'createFromSlots',
            \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
            [$slots]
        );

        for ($i = 1; $i <= 10000; ++$i) {
            $this->assertTrue(
                \Lxj\ClosurePHP\Sugars\Object\call(
                    $bitIntArr,
                    'has',
                    \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                    [$i]
                )
            );
        }

        for ($i = 20001; $i <= 30000; ++$i) {
            $this->assertTrue(
                \Lxj\ClosurePHP\Sugars\Object\call(
                    $bitIntArr,
                    'has',
                    \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                    [$i]
                )
            );
        }

        //Case 4
        $bitIntArr = \Lxj\ClosurePHP\Sugars\Object\newObj(
            'SwFwLess\components\utils\bitmap\bitarray\BitIntArr'
        );

        for ($i = 1; $i <= 10000; $i += 2) {
            \Lxj\ClosurePHP\Sugars\Object\call(
                $bitIntArr,
                'put',
                \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                [$i]
            );
            $this->assertTrue(
                \Lxj\ClosurePHP\Sugars\Object\call(
                    $bitIntArr,
                    'has',
                    \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                    [$i]
                )
            );
        }

        for ($i = 1; $i <= 10000; $i += 2) {
            $this->assertTrue(
                \Lxj\ClosurePHP\Sugars\Object\call(
                    $bitIntArr,
                    'has',
                    \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                    [$i]
                )
            );
        }

        for ($i = 20001; $i <= 30000; $i += 2) {
            \Lxj\ClosurePHP\Sugars\Object\call(
                $bitIntArr,
                'put',
                \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                [$i]
            );
            $this->assertTrue(
                \Lxj\ClosurePHP\Sugars\Object\call(
                    $bitIntArr,
                    'has',
                    \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                    [$i]
                )
            );
        }

        for ($i = 20001; $i <= 30000; $i += 2) {
            $this->assertTrue(
                \Lxj\ClosurePHP\Sugars\Object\call(
                    $bitIntArr,
                    'has',
                    \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                    [$i]
                )
            );
        }

        $slots = \Lxj\ClosurePHP\Sugars\Object\call(
            $bitIntArr,
            'getSlots',
            \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
            []
        );
        $bitIntArr = \Lxj\ClosurePHP\Sugars\Object\callStatic(
            'SwFwLess\components\utils\bitmap\bitarray\BitIntArr',
            'createFromSlots',
            \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
            [$slots]
        );

        for ($i = 1; $i <= 10000; $i += 2) {
            $this->assertTrue(
                \Lxj\ClosurePHP\Sugars\Object\call(
                    $bitIntArr,
                    'has',
                    \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                    [$i]
                )
            );
        }

        for ($i = 20001; $i <= 30000; $i += 2) {
            $this->assertTrue(
                \Lxj\ClosurePHP\Sugars\Object\call(
                    $bitIntArr,
                    'has',
                    \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                    [$i]
                )
            );
        }

        //Case 5
        $bitIntArr = \Lxj\ClosurePHP\Sugars\Object\newObj(
            'SwFwLess\components\utils\bitmap\bitarray\BitIntArr'
        );

        for ($i = 1; $i <= 10000; ++$i) {
            \Lxj\ClosurePHP\Sugars\Object\call(
                $bitIntArr,
                'put',
                \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                [$i]
            );
        }

        for ($i = 1; $i <= 10000; $i += 2) {
            \Lxj\ClosurePHP\Sugars\Object\call(
                $bitIntArr,
                'del',
                \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                [$i]
            );
            $this->assertFalse(
                \Lxj\ClosurePHP\Sugars\Object\call(
                    $bitIntArr,
                    'has',
                    \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                    [$i]
                )
            );
        }

        for ($i = 1; $i <= 10000; ++$i) {
            if (($i % 2) == 0) {
                $this->assertTrue(
                    \Lxj\ClosurePHP\Sugars\Object\call(
                        $bitIntArr,
                        'has',
                        \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                        [$i]
                    )
                );
            } else {
                $this->assertFalse(
                    \Lxj\ClosurePHP\Sugars\Object\call(
                        $bitIntArr,
                        'has',
                        \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                        [$i]
                    )
                );
            }
        }

        $slots = \Lxj\ClosurePHP\Sugars\Object\call(
            $bitIntArr,
            'getSlots',
            \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
            []
        );
        $bitIntArr = \Lxj\ClosurePHP\Sugars\Object\callStatic(
            'SwFwLess\components\utils\bitmap\bitarray\BitIntArr',
            'createFromSlots',
            \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
            [$slots]
        );

        for ($i = 1; $i <= 10000; ++$i) {
            if (($i % 2) == 0) {
                $this->assertTrue(
                    \Lxj\ClosurePHP\Sugars\Object\call(
                        $bitIntArr,
                        'has',
                        \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                        [$i]
                    )
                );
            } else {
                $this->assertFalse(
                    \Lxj\ClosurePHP\Sugars\Object\call(
                        $bitIntArr,
                        'has',
                        \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                        [$i]
                    )
                );
            }
        }

        //Case 6
        $bitIntArr = \Lxj\ClosurePHP\Sugars\Object\newObj(
            'SwFwLess\components\utils\bitmap\bitarray\BitIntArr'
        );

        for ($i = 1; $i <= 10000; ++$i) {
            \Lxj\ClosurePHP\Sugars\Object\call(
                $bitIntArr,
                'put',
                \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                [$i]
            );
        }

        for ($i = 20001; $i <= 30000; ++$i) {
            \Lxj\ClosurePHP\Sugars\Object\call(
                $bitIntArr,
                'put',
                \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                [$i]
            );
        }

        for ($i = 1; $i <= 10000; $i += 2) {
            \Lxj\ClosurePHP\Sugars\Object\call(
                $bitIntArr,
                'del',
                \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                [$i]
            );
            $this->assertFalse(
                \Lxj\ClosurePHP\Sugars\Object\call(
                    $bitIntArr,
                    'has',
                    \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                    [$i]
                )
            );
        }

        for ($i = 20001; $i <= 30000; $i += 2) {
            \Lxj\ClosurePHP\Sugars\Object\call(
                $bitIntArr,
                'del',
                \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                [$i]
            );
            $this->assertFalse(
                \Lxj\ClosurePHP\Sugars\Object\call(
                    $bitIntArr,
                    'has',
                    \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                    [$i]
                )
            );
        }

        for ($i = 1; $i <= 10000; ++$i) {
            if (($i % 2) == 0) {
                $this->assertTrue(
                    \Lxj\ClosurePHP\Sugars\Object\call(
                        $bitIntArr,
                        'has',
                        \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                        [$i]
                    )
                );
            } else {
                $this->assertFalse(
                    \Lxj\ClosurePHP\Sugars\Object\call(
                        $bitIntArr,
                        'has',
                        \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                        [$i]
                    )
                );
            }
        }

        for ($i = 20001; $i <= 30000; ++$i) {
            if (($i % 2) == 0) {
                $this->assertTrue(
                    \Lxj\ClosurePHP\Sugars\Object\call(
                        $bitIntArr,
                        'has',
                        \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                        [$i]
                    )
                );
            } else {
                $this->assertFalse(
                    \Lxj\ClosurePHP\Sugars\Object\call(
                        $bitIntArr,
                        'has',
                        \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                        [$i]
                    )
                );
            }
        }

        $slots = \Lxj\ClosurePHP\Sugars\Object\call(
            $bitIntArr,
            'getSlots',
            \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
            []
        );
        $bitIntArr = \Lxj\ClosurePHP\Sugars\Object\callStatic(
            'SwFwLess\components\utils\bitmap\bitarray\BitIntArr',
            'createFromSlots',
            \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
            [$slots]
        );

        for ($i = 1; $i <= 10000; ++$i) {
            if (($i % 2) == 0) {
                $this->assertTrue(
                    \Lxj\ClosurePHP\Sugars\Object\call(
                        $bitIntArr,
                        'has',
                        \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                        [$i]
                    )
                );
            } else {
                $this->assertFalse(
                    \Lxj\ClosurePHP\Sugars\Object\call(
                        $bitIntArr,
                        'has',
                        \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                        [$i]
                    )
                );
            }
        }

        for ($i = 20001; $i <= 30000; ++$i) {
            if (($i % 2) == 0) {
                $this->assertTrue(
                    \Lxj\ClosurePHP\Sugars\Object\call(
                        $bitIntArr,
                        'has',
                        \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                        [$i]
                    )
                );
            } else {
                $this->assertFalse(
                    \Lxj\ClosurePHP\Sugars\Object\call(
                        $bitIntArr,
                        'has',
                        \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                        [$i]
                    )
                );
            }
        }

        //Case 7
        $bitIntArr = \Lxj\ClosurePHP\Sugars\Object\newObj(
            'SwFwLess\components\utils\bitmap\bitarray\BitIntArr'
        );

        for ($i = 1; $i <= 10000; ++$i) {
            \Lxj\ClosurePHP\Sugars\Object\call(
                $bitIntArr,
                'put',
                \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                [$i]
            );
        }

        $iterator = \Lxj\ClosurePHP\Sugars\Object\call(
            $bitIntArr,
            'iterator',
            \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
            []
        );
        $i = 0;
        foreach ($iterator as $number) {
            $this->assertEquals(++$i, $number);
        }

        $slots = \Lxj\ClosurePHP\Sugars\Object\call(
            $bitIntArr,
            'getSlots',
            \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
            []
        );
        $bitIntArr = \Lxj\ClosurePHP\Sugars\Object\callStatic(
            'SwFwLess\components\utils\bitmap\bitarray\BitIntArr',
            'createFromSlots',
            \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
            [$slots]
        );

        $iterator = \Lxj\ClosurePHP\Sugars\Object\call(
            $bitIntArr,
            'iterator',
            \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
            []
        );
        $i = 0;
        foreach ($iterator as $number) {
            $this->assertEquals(++$i, $number);
        }

        //Case 8
        $bitIntArr = \Lxj\ClosurePHP\Sugars\Object\newObj(
            'SwFwLess\components\utils\bitmap\bitarray\BitIntArr'
        );

        for ($i = 1; $i <= 10000; $i += 2) {
            \Lxj\ClosurePHP\Sugars\Object\call(
                $bitIntArr,
                'put',
                \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                [$i]
            );
        }

        $iterator = \Lxj\ClosurePHP\Sugars\Object\call(
            $bitIntArr,
            'iterator',
            \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
            []
        );
        $i = 1;
        foreach ($iterator as $number) {
            $this->assertEquals($i, $number);
            $i += 2;
        }

        $slots = \Lxj\ClosurePHP\Sugars\Object\call(
            $bitIntArr,
            'getSlots',
            \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
            []
        );
        $bitIntArr = \Lxj\ClosurePHP\Sugars\Object\callStatic(
            'SwFwLess\components\utils\bitmap\bitarray\BitIntArr',
            'createFromSlots',
            \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
            [$slots]
        );

        $iterator = \Lxj\ClosurePHP\Sugars\Object\call(
            $bitIntArr,
            'iterator',
            \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
            []
        );
        $i = 1;
        foreach ($iterator as $number) {
            $this->assertEquals($i, $number);
            $i += 2;
        }

        //Case 9
        $bitIntArr = \Lxj\ClosurePHP\Sugars\Object\newObj(
            'SwFwLess\components\utils\bitmap\bitarray\BitIntArr'
        );

        for ($i = 1; $i <= 10000; ++$i) {
            \Lxj\ClosurePHP\Sugars\Object\call(
                $bitIntArr,
                'put',
                \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                [$i]
            );
        }

        for ($i = 20001; $i <= 30000; ++$i) {
            \Lxj\ClosurePHP\Sugars\Object\call(
                $bitIntArr,
                'put',
                \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                [$i]
            );
        }

        $iterator = \Lxj\ClosurePHP\Sugars\Object\call(
            $bitIntArr,
            'iterator',
            \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
            []
        );
        $i = 0;
        foreach ($iterator as $number) {
            $this->assertEquals(++$i, $number);
            if ($i === 10000) {
                $i = 20000;
            }
        }

        $slots = \Lxj\ClosurePHP\Sugars\Object\call(
            $bitIntArr,
            'getSlots',
            \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
            []
        );
        $bitIntArr = \Lxj\ClosurePHP\Sugars\Object\callStatic(
            'SwFwLess\components\utils\bitmap\bitarray\BitIntArr',
            'createFromSlots',
            \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
            [$slots]
        );

        $iterator = \Lxj\ClosurePHP\Sugars\Object\call(
            $bitIntArr,
            'iterator',
            \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
            []
        );
        $i = 0;
        foreach ($iterator as $number) {
            $this->assertEquals(++$i, $number);
            if ($i === 10000) {
                $i = 20000;
            }
        }

        //Case 10
        $bitIntArr = \Lxj\ClosurePHP\Sugars\Object\newObj(
            'SwFwLess\components\utils\bitmap\bitarray\BitIntArr'
        );

        for ($i = 1; $i <= 10000; $i += 2) {
            \Lxj\ClosurePHP\Sugars\Object\call(
                $bitIntArr,
                'put',
                \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                [$i]
            );
        }

        for ($i = 20001; $i <= 30000; $i += 2) {
            \Lxj\ClosurePHP\Sugars\Object\call(
                $bitIntArr,
                'put',
                \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
                [$i]
            );
        }

        $iterator = \Lxj\ClosurePHP\Sugars\Object\call(
            $bitIntArr,
            'iterator',
            \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
            []
        );
        $i = 1;
        foreach ($iterator as $number) {
            $this->assertEquals($i, $number);
            $i += 2;
            if ($i > 10000 && $i < 20001) {
                $i = 20001;
            }
        }

        $slots = \Lxj\ClosurePHP\Sugars\Object\call(
            $bitIntArr,
            'getSlots',
            \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
            []
        );
        $bitIntArr = \Lxj\ClosurePHP\Sugars\Object\callStatic(
            'SwFwLess\components\utils\bitmap\bitarray\BitIntArr',
            'createFromSlots',
            \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
            [$slots]
        );

        $iterator = \Lxj\ClosurePHP\Sugars\Object\call(
            $bitIntArr,
            'iterator',
            \Lxj\ClosurePHP\Sugars\Scope::PUBLIC,
            []
        );
        $i = 1;
        foreach ($iterator as $number) {
            $this->assertEquals($i, $number);
            $i += 2;
            if ($i > 10000 && $i < 20001) {
                $i = 20001;
            }
        }
    }
}
