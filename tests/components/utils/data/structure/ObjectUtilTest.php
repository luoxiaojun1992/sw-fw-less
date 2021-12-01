<?php

namespace SwFwLessTest\components\utils\data\structure;

class ObjectUtilTest extends \PHPUnit\Framework\TestCase
{
    public function testSingleObjectToArray()
    {
        $object = new \stdClass();
        $object->foo = 'bar';
        $object->bar = 'foo';

        $arr = \SwFwLess\components\utils\ObjectUtil::toArray($object);

        $this->assertIsArray($arr);
        $this->assertArrayHasKey('foo', $arr);
        $this->assertArrayHasKey('bar', $arr);
        $this->assertEquals('bar', $arr['foo']);
        $this->assertEquals('foo', $arr['bar']);
    }

    public function testNestedObjectToArray()
    {
        $nestedObject = new \stdClass();
        $nestedObject->arr = ['foo', 'bar'];
        $nestedObject->foo = 'bar';
        $nestedObject->bar = 'foo';

        $object = new \stdClass();
        $object->foo = 'bar';
        $object->bar = 'foo';
        $object->obj = $nestedObject;
        $object->arr = ['foo', 'bar'];

        $arr = \SwFwLess\components\utils\ObjectUtil::toArray($object);

        $this->assertIsArray($arr);
        $this->assertArrayHasKey('foo', $arr);
        $this->assertArrayHasKey('bar', $arr);
        $this->assertArrayHasKey('obj', $arr);
        $this->assertArrayHasKey('arr', $arr);
        $this->assertEquals('bar', $arr['foo']);
        $this->assertEquals('foo', $arr['bar']);
        $this->assertIsArray($arr['arr']);
        $this->assertArrayHasKey(0, $arr['arr']);
        $this->assertArrayHasKey(1, $arr['arr']);
        $this->assertEquals('foo', $arr['arr'][0]);
        $this->assertEquals('bar', $arr['arr'][1]);

        $nestedArr = $arr['obj'];
        $this->assertIsArray($nestedArr);
        $this->assertArrayHasKey('foo', $nestedArr);
        $this->assertArrayHasKey('bar', $nestedArr);
        $this->assertArrayHasKey('arr', $nestedArr);
        $this->assertEquals('bar', $nestedArr['foo']);
        $this->assertEquals('foo', $nestedArr['bar']);
        $this->assertIsArray($nestedArr['arr']);
        $this->assertArrayHasKey(0, $nestedArr['arr']);
        $this->assertArrayHasKey(1, $nestedArr['arr']);
        $this->assertEquals('foo', $nestedArr['arr'][0]);
        $this->assertEquals('bar', $nestedArr['arr'][1]);
    }
}
