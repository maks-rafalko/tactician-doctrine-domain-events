Tactician Domain Events - Doctrine Bridge
=========================================

[![Build Status](https://travis-ci.org/borNfreee/tactician-doctrine-domain-events.svg?branch=master)](https://travis-ci.org/borNfreee/tactician-doctrine-domain-events)
[![codecov](https://codecov.io/gh/borNfreee/tactician-doctrine-domain-events/branch/master/graph/badge.svg)](https://codecov.io/gh/borNfreee/tactician-doctrine-domain-events)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/borNfreee/tactician-domain-events/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/borNfreee/tactician-domain-events/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/bornfree/tactician-doctrine-domain-events/v/stable)](https://packagist.org/packages/bornfree/tactician-doctrine-domain-events)

The bridge to provide Domain Events handling with Tactician command bus and Doctrine ORM

Installation
------------

Install via composer

```bash
composer require bornfree/tactician-doctrine-domain-events
```

Usage
-----

Using the [events recorder facilities](https://maks-rafalko.github.io/tactician-domain-events/doc/domain_events.html#record-events-in-entity) you can let Doctrine ORM collect domain events and subsequently let the `EventDispatcher` handle them.

Make sure that your entities implement the `ContainsRecordedMessages` interface. Use the
`EventRecorderCapabilities` trait from [Tactician Domain Events](https://maks-rafalko.github.io/tactician-domain-events) library to conveniently record events from inside the entity:

```php
use BornFree\TacticianDomainEvent\Recorder\ContainsRecordedEvents;
use BornFree\TacticianDomainEvent\Recorder\EventRecorderCapabilities;

class Task implements ContainsRecordedMessages
{
    use EventRecorderCapabilities;

    public function __construct($name)
    {
        $this->record(new TaskWasCreated($name));
    }
}
```

Then set up the *event recorder* for Doctrine entities:

```php
use BornFree\TacticianDoctrineDomainEvent\EventListener\CollectsEventsFromEntities;

$eventRecorder = new CollectsEventsFromEntities();

$entityManager->getConnection()->getEventManager()->addEventSubscriber($eventRecorder);
```

> ##### Syfmony integration
> This listener will be registered automatically with Symfony, see the [documentation](https://maks-rafalko.github.io/tactician-domain-events-bundle)

The event recorder will loop over all the entities that were involved in the last database transaction and collect their
internally recorded events.

After a database transaction was completed successfully these events should be handled by the `EventDispatcher`. This is done by
a specialized middleware, which should be added to the command bus *before* the middleware that is responsible for
handling the transaction.

```php
use League\Tactician\CommandBus;
use League\Tactician\Doctrine\ORM\TransactionMiddleware;
use namespace BornFree\TacticianDomainEvent\Middleware\ReleaseRecordedEventsMiddleware;

// see the previous sections about $eventRecorder and $eventDispatcher
$releaseRecordedEventsMiddleware = new ReleaseRecordedEventsMiddleware($eventRecorder, $eventDispatcher);

$commandBus = new CommandBus(
    [
        $releaseRecordedEventsMiddleware, // it should be before transaction middleware
        $transactionMiddleware,
        $commandHandlerMiddleware
    ]
);
```


License
-------

Copyright (c) 2017, Maks Rafalko

Under MIT license, read LICENSE file.
