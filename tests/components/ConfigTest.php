<?php

use Mockery as M;

class ConfigTest extends \PHPUnit\Framework\TestCase
{
    public function afterTest()
    {
        parent::tearDown();
        \SwFwLess\components\Config::clear();
    }

    protected function mockSwooleScheduler()
    {
        $mockScheduler = M::mock('alias:' . 'SwFwLess\components\swoole\Scheduler');
        $mockScheduler->shouldReceive('withoutPreemptive')
            ->with(M::type('callable'))
            ->andReturnUsing(function ($arg) {
                return call_user_func($arg);
            });
    }

    public function testSetAndGet()
    {
        $this->mockSwooleScheduler();

        \SwFwLess\components\Config::initByArr([
            'foo' => 'bar'
        ]);

        $this->assertEquals(
            'bar',
            \SwFwLess\components\Config::get('foo')
        );

        \SwFwLess\components\Config::set('foo', 'bar1');
        $this->assertEquals(
            'bar1',
            \SwFwLess\components\Config::get('foo')
        );

        \SwFwLess\components\Config::set('foo2', 'bar2');
        $this->assertEquals(
            'bar2',
            \SwFwLess\components\Config::get('foo2')
        );

        $this->afterTest();
    }

    public function testSetAndForget()
    {
        $this->mockSwooleScheduler();

        \SwFwLess\components\Config::initByArr([
            'foo' => 'bar'
        ]);

        $this->assertTrue(
            \SwFwLess\components\Config::has('foo')
        );

        \SwFwLess\components\Config::forget('foo');
        $this->assertFalse(
            \SwFwLess\components\Config::has('foo')
        );

        \SwFwLess\components\Config::set('foo', 'bar1');
        $this->assertTrue(
            \SwFwLess\components\Config::has('foo')
        );

        \SwFwLess\components\Config::forget('foo');
        $this->assertFalse(
            \SwFwLess\components\Config::has('foo')
        );

        \SwFwLess\components\Config::set('foo2', 'bar2');
        $this->assertTrue(
            \SwFwLess\components\Config::has('foo2')
        );

        \SwFwLess\components\Config::forget('foo2');
        $this->assertFalse(
            \SwFwLess\components\Config::has('foo2')
        );

        $this->afterTest();
    }

    public function testSetAndHas()
    {
        $this->mockSwooleScheduler();

        \SwFwLess\components\Config::initByArr([
            'foo' => 'bar'
        ]);

        $this->assertTrue(
            \SwFwLess\components\Config::has('foo')
        );

        \SwFwLess\components\Config::set('foo', 'bar1');
        $this->assertTrue(
            \SwFwLess\components\Config::has('foo')
        );

        \SwFwLess\components\Config::set('foo2', 'bar2');
        $this->assertTrue(
            \SwFwLess\components\Config::has('foo2')
        );

        $this->afterTest();
    }

    public function testSetGetAndForget()
    {
        $this->mockSwooleScheduler();

        \SwFwLess\components\Config::initByArr([
            'foo' => 'bar'
        ]);

        $this->assertTrue(
            \SwFwLess\components\Config::has('foo')
        );

        \SwFwLess\components\Config::forget('foo');
        $this->assertFalse(
            \SwFwLess\components\Config::has('foo')
        );
        $this->assertNull(
            \SwFwLess\components\Config::get('foo')
        );
        $this->assertEquals(
            'defaultBar',
            \SwFwLess\components\Config::get('foo', 'defaultBar')
        );

        \SwFwLess\components\Config::set('foo', 'bar1');
        $this->assertTrue(
            \SwFwLess\components\Config::has('foo')
        );
        $this->assertEquals(
            'bar1',
            \SwFwLess\components\Config::get('foo')
        );

        \SwFwLess\components\Config::forget('foo');
        $this->assertFalse(
            \SwFwLess\components\Config::has('foo')
        );
        $this->assertNull(
            \SwFwLess\components\Config::get('foo')
        );
        $this->assertEquals(
            'defaultBar1',
            \SwFwLess\components\Config::get('foo', 'defaultBar1')
        );

        \SwFwLess\components\Config::set('foo2', 'bar2');
        $this->assertTrue(
            \SwFwLess\components\Config::has('foo2')
        );

        \SwFwLess\components\Config::forget('foo2');
        $this->assertFalse(
            \SwFwLess\components\Config::has('foo2')
        );
        $this->assertNull(
            \SwFwLess\components\Config::get('foo2')
        );
        $this->assertEquals(
            'defaultBar2',
            \SwFwLess\components\Config::get('foo', 'defaultBar2')
        );

        $this->afterTest();
    }

    public function testGetDefault()
    {
        $this->mockSwooleScheduler();

        \SwFwLess\components\Config::initByArr([
            'foo' => 'bar'
        ]);

        $this->assertEquals(
            'bar3',
            \SwFwLess\components\Config::get('foo3', 'bar3')
        );

        \SwFwLess\components\Config::set('foo', 'bar1');
        $this->assertEquals(
            'bar_5',
            \SwFwLess\components\Config::get('foo5', 'bar_5')
        );

        \SwFwLess\components\Config::set('foo2', 'bar2');
        $this->assertEquals(
            'bar_6',
            \SwFwLess\components\Config::get('foo6', 'bar_6')
        );

        $this->afterTest();
    }
}
