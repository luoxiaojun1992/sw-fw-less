<?php

class VariableTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @throws Exception
     */
    public function testAssignCorrectValue()
    {
        $testVar = 'foo';
        \SwFwLess\components\utils\Variable::assignValue($testVar, 'bar');
        $this->assertEquals('bar', $testVar);
    }

    /**
     * @throws Exception
     */
    public function testAssignIncorrectValue()
    {
        $testVar = 'foo';
        $this->expectExceptionMessage('Type error');
        \SwFwLess\components\utils\Variable::assignValue($testVar, false);
    }

    /**
     * @throws Exception
     */
    public function testAssignValueToReferenceVar()
    {
        $testVar = 'foo';
        $referenceVar = &$testVar;
        \SwFwLess\components\utils\Variable::assignValue($referenceVar, 'bar');
        $this->assertEquals('bar', $referenceVar);
        $this->assertEquals('bar', $testVar);
    }

    public function testOneNull()
    {
        $this->assertTrue(
            \SwFwLess\components\utils\Variable::oneNull(
                null,
                true,
                null
            )
        );

        $this->assertTrue(
            \SwFwLess\components\utils\Variable::oneNull(
                null,
                null,
                null
            )
        );

        $this->assertFalse(
            \SwFwLess\components\utils\Variable::oneNull(
                true,
                true,
                true
            )
        );
    }

    public function testAllNull()
    {
        $this->assertTrue(
            \SwFwLess\components\utils\Variable::allNull(
                null,
                null,
                null
            )
        );

        $this->assertFalse(
            \SwFwLess\components\utils\Variable::allNull(
                null,
                true,
                null
            )
        );

        $this->assertFalse(
            \SwFwLess\components\utils\Variable::allNull(
                true,
                true,
                true
            )
        );
    }

    public function testOneNotNull()
    {
        $this->assertTrue(
            \SwFwLess\components\utils\Variable::oneNotNull(
                null,
                true,
                null
            )
        );

        $this->assertTrue(
            \SwFwLess\components\utils\Variable::oneNotNull(
                true,
                true,
                true
            )
        );

        $this->assertFalse(
            \SwFwLess\components\utils\Variable::oneNotNull(
                null,
                null,
                null
            )
        );
    }

    public function testAllNotNull()
    {
        $this->assertTrue(
            \SwFwLess\components\utils\Variable::allNotNull(
                true,
                true,
                true
            )
        );

        $this->assertFalse(
            \SwFwLess\components\utils\Variable::allNotNull(
                true,
                null,
                true
            )
        );

        $this->assertFalse(
            \SwFwLess\components\utils\Variable::allNotNull(
                null,
                null,
                null
            )
        );
    }
}
