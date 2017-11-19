<?php

namespace BornFree\TacticianDoctrineDomainEvent\Tests\EventListener;

use BornFree\TacticianDoctrineDomainEvent\EventListener\CollectsEventsFromAllEntitiesManagedByUnitOfWork;
use BornFree\TacticianDomainEvent\Recorder\ContainsRecordedEvents;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\UnitOfWork;

class CollectsEventsFromAllEntitiesManagedByUnitOfWorkTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CollectsEventsFromAllEntitiesManagedByUnitOfWork
     */
    private $listener;

    public function setUp()
    {
        $this->listener = new CollectsEventsFromAllEntitiesManagedByUnitOfWork();
    }

    /**
     * @test
     */
    public function it_subscribes_to_post_flush_event()
    {
        $events = $this->listener->getSubscribedEvents();

        $this->assertEquals([Events::postFlush], $events);
    }

    /**
     * @test
     */
    public function it_erases_events()
    {
        $event1 = new \stdClass();
        $event2 = new \stdClass();

        $expectedEvents = [$event1, $event2];
        $postFlushEventArgs = $this->getPostFlushEventArgsMock($expectedEvents);

        $this->listener->postFlush($postFlushEventArgs);

        $collectedEvents = $this->listener->releaseEvents();

        $this->assertCount(2, $collectedEvents);

        $this->listener->eraseEvents();

        $this->assertCount(0, $this->listener->releaseEvents());
    }

    /**
     * @test
     */
    public function it_collects_events_on_lifecycle_event()
    {
        $event1 = new \stdClass();
        $event2 = new \stdClass();

        $expectedEvents = [$event1, $event2];

        $postFlushEventArgs = $this->getPostFlushEventArgsMock($expectedEvents);

        $this->listener->postFlush($postFlushEventArgs);

        $collectedEvents = $this->listener->releaseEvents();

        $this->assertEquals($expectedEvents, $collectedEvents);
    }

    /**
     * @param $expectedEvents
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getPostFlushEventArgsMock($expectedEvents)
    {
        $entity = $this->getMockBuilder(ContainsRecordedEvents::class)
            ->setMethods(['releaseEvents', 'eraseEvents'])
            ->getMock();

        $entity->expects($this->once())
            ->method('releaseEvents')
            ->will($this->returnValue($expectedEvents));

        $entityManager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['getUnitOfWork'])
            ->getMock();

        $uow = $this->getMockBuilder(UnitOfWork::class)
            ->disableOriginalConstructor()
            ->setMethods(['getIdentityMap'])
            ->getMock();

        $postFlushEventArgs = $this->getMockBuilder(PostFlushEventArgs::class)
            ->disableOriginalConstructor()
            ->setMethods(['getEntityManager'])
            ->getMock();

        $postFlushEventArgs->expects($this->once())
            ->method('getEntityManager')
            ->will($this->returnValue($entityManager));

        $entityManager->expects($this->once())
            ->method('getUnitOfWork')
            ->will($this->returnValue($uow));

        $uow->expects($this->once())
            ->method('getIdentityMap')
            ->will($this->returnValue([
                'className' => [$entity]
            ]));

        return $postFlushEventArgs;
    }
}