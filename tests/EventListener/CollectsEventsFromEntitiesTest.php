<?php

namespace BornFree\TacticianDoctrineDomainEvent\Tests\EventListener;

use BornFree\TacticianDoctrineDomainEvent\EventListener\CollectsEventsFromEntities;
use BornFree\TacticianDomainEvent\Recorder\ContainsRecordedEvents;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;

class CollectsEventsFromEntitiesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CollectsEventsFromEntities
     */
    private $listener;

    public function setUp()
    {
        $this->listener = new CollectsEventsFromEntities();
    }

    /**
     * @test
     */
    public function it_subscribes_to_all_needed_events()
    {
        $expectedEvents = [
            Events::postPersist,
            Events::postUpdate,
            Events::postRemove,
        ];
        $events = $this->listener->getSubscribedEvents();

        $this->assertEquals($expectedEvents, $events);
    }

    /**
     * @test
     */
    public function it_erases_events()
    {
        $event1 = new \stdClass();
        $event2 = new \stdClass();

        $expectedEvents = [$event1, $event2];
        $lifecycleEvent = $this->getLifecycleEventMock($expectedEvents);

        $this->listener->postPersist($lifecycleEvent);

        $collectedEvents = $this->listener->releaseEvents();

        $this->assertCount(2, $collectedEvents);

        $this->listener->eraseEvents();

        $this->assertCount(0, $this->listener->releaseEvents());
    }

    /**
     * @test
     * @dataProvider lifecycleEventNameProvider
     */
    public function it_collects_events_on_lifecycle_event($lifecycleEventName)
    {
        $event1 = new \stdClass();
        $event2 = new \stdClass();

        $expectedEvents = [$event1, $event2];

        $lifecycleEvent = $this->getLifecycleEventMock($expectedEvents);

        $this->listener->{$lifecycleEventName}($lifecycleEvent);

        $collectedEvents = $this->listener->releaseEvents();

        $this->assertEquals($expectedEvents, $collectedEvents);
    }

    public function lifecycleEventNameProvider()
    {
        return [
            ['postPersist'],
            ['postUpdate'],
            ['postRemove'],
        ];
    }

    /**
     * @param $expectedEvents
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getLifecycleEventMock($expectedEvents)
    {
        $entity = $this->getMockBuilder(ContainsRecordedEvents::class)
            ->setMethods(['releaseEvents', 'eraseEvents'])
            ->getMock();

        $entity->expects($this->once())
            ->method('releaseEvents')
            ->will($this->returnValue($expectedEvents));

        $lifecycleEvent = $this->getMockBuilder(LifecycleEventArgs::class)
            ->disableOriginalConstructor()
            ->setMethods(['getEntity'])
            ->getMock();

        $lifecycleEvent->expects($this->once())
            ->method('getEntity')
            ->will($this->returnValue($entity));

        return $lifecycleEvent;
    }
}