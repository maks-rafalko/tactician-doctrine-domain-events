<?php

namespace BornFree\TacticianDoctrineDomainEvent\EventListener;

use BornFree\TacticianDomainEvent\Recorder\ContainsRecordedEvents;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;

/**
 * Class CollectsEventsFromEntities
 * @package BornFree\TacticianDoctrineDomainEvent\EventListener
 */
class CollectsEventsFromEntities implements EventSubscriber, ContainsRecordedEvents
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
            Events::postPersist,
            Events::postUpdate,
            Events::postRemove,
        ];
    }

    /**
     * @param LifecycleEventArgs $event
     */
    public function postPersist(LifecycleEventArgs $event)
    {
        $this->collectEventsFromEntity($event);
    }

    /**
     * @param LifecycleEventArgs $event
     */
    public function postUpdate(LifecycleEventArgs $event)
    {
        $this->collectEventsFromEntity($event);
    }

    /**
     * @param LifecycleEventArgs $event
     */
    public function postRemove(LifecycleEventArgs $event)
    {
        $this->collectEventsFromEntity($event);
    }

    /**
     * @return array
     */
    public function releaseEvents()
    {
        return $this->collectedEvents;
    }

    /**
     * Erases collected events from the entities
     */
    public function eraseEvents()
    {
        $this->collectedEvents = [];
    }

    /**
     * @param LifecycleEventArgs $lifecycleEventArgs
     */
    private function collectEventsFromEntity(LifecycleEventArgs $lifecycleEventArgs)
    {
        /** @var ContainsRecordedEvents $entity */
        $entity = $lifecycleEventArgs->getEntity();

        if ($entity instanceof ContainsRecordedEvents) {
            foreach ($entity->releaseEvents() as $event) {
                $this->collectedEvents[] = $event;
            }
        }
    }
}
