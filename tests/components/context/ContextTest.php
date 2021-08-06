<?php

class ContextTest extends \PHPUnit\Framework\TestCase
{
    public function testDataOperation()
    {
        $parentContext = \SwFwLess\components\context\Context::create()
            ->setAll(['id' => 1])
            ->set('foo', 'bar')
            ->withReturn(function ($data, $childData) {
                $this->assertEquals(['child return data'], $childData);
                $this->assertEquals('return_data', $data);
                return 'return data';
            });

        $context = \SwFwLess\components\context\Context::create()->withParent($parentContext)
            ->setAll(['id' => 2])
            ->withReturn(function ($data, $childData) {
                $this->assertEquals([], $childData);
                $this->assertEquals('return_data', $data);
                return 'child return data';
            });

        $this->assertTrue($parentContext->has('id'));
        $this->assertEquals(1, $parentContext->get('id'));
        $parentContext->forget('id');
        $this->assertFalse($parentContext->has('id'));
        $this->assertNull($parentContext->get('id'));

        $this->assertTrue($parentContext->has('foo'));
        $this->assertEquals('bar', $parentContext->get('foo'));
        $parentContext->clear();
        $this->assertFalse($parentContext->has('foo'));
        $this->assertNull($parentContext->get('foo'));

        $this->assertEquals(['child return data'], $context->returnContext('return_data'));
        $this->assertEquals(['child return data', 'return data'], $parentContext->returnContext('return_data'));

        $this->assertTrue($parentContext->childContext()->has('id'));
        $this->assertEquals(2, $parentContext->childContext()->get('id'));
        $parentContext->childContext()->forget('id');
        $this->assertFalse($parentContext->childContext()->has('id'));
        $this->assertNull($parentContext->childContext()->get('id'));
    }
}
