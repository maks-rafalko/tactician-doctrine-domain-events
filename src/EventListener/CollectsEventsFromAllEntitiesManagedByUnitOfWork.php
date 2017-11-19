<?php

namespace BornFree\TacticianDoctrineDomainEvent\EventListener;

use BornFree\TacticianDomainEvent\Recorder\ContainsRecordedEvents;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Events;

/**
 * Class CollectsEventsFromAllEntitiesManagedByUnitOfWork
 * @package BornFree\TacticianDoctrineDomainEvent\EventListener
 */
class CollectsEventsFromAllEntitiesManagedByUnitOfWork implements EventSubscriber, ContainsRecordedEvents
{
    /**
     * @var array
     */
    private $collectedEvents = [];

    /**
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            Events::postFlush
        ];
    }

    /**
     * @param PostFlushEventArgs $event
     */
    public function postFlush(PostFlushEventArgs $event)
    {
        $uow = $event->getEntityManager()->getUnitOfWork();

        foreach ($uow->getIdentityMap() as $entities) {
            foreach ($entities as $entity){
                $this->collectEventsFromEntity($entity);
            }
        }
    }

    /**
     * @return array
     */
    public function releaseEvents()
    {
        $events = $this->collectedEvents;
        $this->eraseEvents();

        return $events;
    }

    /**
     * Erases collected events from the entities
     */
    public function eraseEvents()
    {
        $this->collectedEvents = [];
    }

    /**
     * @param mixed $entity
     */
    private function collectEventsFromEntity($entity)
    {
        if ($entity instanceof ContainsRecordedEvents) {
            foreach ($entity->releaseEvents() as $event) {
                $this->collectedEvents[] = $event;
            }
        }
    }
}
