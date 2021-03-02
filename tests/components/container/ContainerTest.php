<?php

class ContainerTest extends \PHPUnit\Framework\TestCase
{
    public function testDataOperation()
    {
        $container = \SwFwLess\components\container\Container::createByData(['id' => 1]);

        $this->assertEquals(1, $container->get('id'));

        $container->set('foo', 'bar');

        $this->assertEquals('bar', $container->get('foo'));
        $this->assertEquals(1, $container->get('id'));
    }
}
