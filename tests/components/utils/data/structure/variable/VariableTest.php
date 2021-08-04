<?php

namespace SwFwLessTest\components\utils\data\structure\variable;

use SwFwLess\components\utils\data\structure\variable\Variable;

class VariableTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @throws Exception
     */
    public function testAssignCorrectValue()
    {
        $testVar = 'foo';
        Variable::assignValue($testVar, 'bar');
        $this->assertEquals('bar', $testVar);
    }

    /**
     * @throws Exception
     */
    public function testAssignIncorrectValue()
    {
        $testVar = 'foo';
        $this->expectExceptionMessage('Type error');
        Variable::assignValue($testVar, false);
    }

    /**
     * @throws Exception
     */
    public function testAssignValueToReferenceVar()
    {
        $testVar = 'foo';
        $referenceVar = &$testVar;
        Variable::assignValue($referenceVar, 'bar');
        $this->assertEquals('bar', $referenceVar);
        $this->assertEquals('bar', $testVar);
    }

    public function testOneNull()
    {
        $this->assertTrue(
            Variable::oneNull(
                null,
                true,
                null
            )
        );

        $this->assertTrue(
            Variable::oneNull(
                null,
                null,
                null
            )
        );

        $this->assertFalse(
            Variable::oneNull(
                true,
                true,
                true
            )
        );
    }

    public function testAllNull()
    {
        $this->assertTrue(
            Variable::allNull(
                null,
                null,
                null
            )
        );

        $this->assertFalse(
            Variable::allNull(
                null,
                true,
                null
            )
        );

        $this->assertFalse(
            Variable::allNull(
                true,
                true,
                true
            )
        );
    }

    public function testOneNotNull()
    {
        $this->assertTrue(
            Variable::oneNotNull(
                null,
                true,
                null
            )
        );

        $this->assertTrue(
            Variable::oneNotNull(
                true,
                true,
                true
            )
        );

        $this->assertFalse(
            Variable::oneNotNull(
                null,
                null,
                null
            )
        );
    }

    public function testAllNotNull()
    {
        $this->assertTrue(
            Variable::allNotNull(
                true,
                true,
                true
            )
        );

        $this->assertFalse(
            Variable::allNotNull(
                true,
                null,
                true
            )
        );

        $this->assertFalse(
            Variable::allNotNull(
                null,
                null,
                null
            )
        );
    }
}
