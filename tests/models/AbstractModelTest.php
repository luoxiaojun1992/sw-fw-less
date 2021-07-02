<?php

class AbstractModelTest extends \PHPUnit\Framework\TestCase
{
    public function beforeTest()
    {
        parent::setUp();
        \SwFwLess\components\Config::initByArr([
            'events' => [],
        ]);
        \SwFwLess\components\event\EventProvider::bootWorker();
    }

    protected function afterTest()
    {
        parent::tearDown();
        \SwFwLess\components\event\Event::clearInstance();
        \SwFwLess\components\Config::clear();
    }

    protected function getTestModel()
    {
        require_once __DIR__ . '/../stubs/models/Test.php';

        return new Test();
    }

    public function testEvent()
    {
        $this->beforeTest();

        $testModel = $this->getTestModel();

        $this->assertNull($testModel->id);
        $this->assertNull($testModel->foo);

        $testModel->save();

        $this->assertEquals(1, $testModel->id);
        $this->assertEquals('bar', $testModel->foo);

        $this->afterTest();
    }
}
