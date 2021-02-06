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
}
