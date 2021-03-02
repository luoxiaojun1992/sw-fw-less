<?php

class ContextTest extends \PHPUnit\Framework\TestCase
{
    public function testDataOperation()
    {
        $parentContext = \SwFwLess\components\context\Context::create()
            ->setAll(['id' => 1]);

        $context = \SwFwLess\components\context\Context::create()->withParent($parentContext)
            ->setAll(['id' => 2])
            ->set('foo', 'bar')
            ->withReturn(function ($data) {
                $this->assertEquals('return_data', $data);
                return 'received';
            });

        $this->assertTrue($context->has('id'));
        $this->assertEquals(2, $context->get('id'));
        $context->forget('id');
        $this->assertFalse($context->has('id'));
        $this->assertNull($context->get('id'));

        $this->assertTrue($context->has('foo'));
        $this->assertEquals('bar', $context->get('foo'));
        $context->clear();
        $this->assertFalse($context->has('foo'));
        $this->assertNull($context->get('foo'));

        $this->assertEquals('received', $context->returnContext('return_data'));

        $this->assertTrue($context->parentContext()->has('id'));
        $this->assertEquals(1, $context->parentContext()->get('id'));
        $context->parentContext()->forget('id');
        $this->assertFalse($context->parentContext()->has('id'));
        $this->assertNull($context->parentContext()->get('id'));
    }
}
