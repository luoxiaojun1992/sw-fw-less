<?php

namespace SwFwLessTest\components\event;

use PHPUnit\Framework\TestCase;
use SwFwLess\components\Config;
use SwFwLess\facades\Event;

class EventTest extends TestCase
{
    public function afterTest()
    {
        parent::tearDown();

        \SwFwLess\components\event\Event::clearInstance();
        Config::clear();
    }

    public function testTriggerAndListener()
    {
        $eventPayload = [];
        Config::initByArr(['events' => []]);
        Event::on('test.event', function (\Cake\Event\Event $event) use (&$eventPayload) {
            $eventPayload = $event->getData();
        });
        Event::dispatch(
            new \Cake\Event\Event(
                'test.event', null, ['payload' => 'test']
            )
        );
        $this->assertEquals(['payload' => 'test'], $eventPayload);

        $this->afterTest();
    }
}
