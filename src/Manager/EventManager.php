<?php

namespace App\Manager;

use App\Entity\Event;
use App\Entity\Message;
use App\Entity\MessageThread;
use App\Entity\Signalement;
use App\Factory\EventFactory;
use Doctrine\Persistence\ManagerRegistry;

class EventManager extends AbstractManager
{
    public function __construct(private EventFactory $eventFactory, protected ManagerRegistry $managerRegistry, protected string $entityName = Event::class)
    {
        $this->managerRegistry = $managerRegistry;
        $this->entityName = $entityName;
    }

    public function createEventMessage(
        MessageThread $messageThread,
        string $title,
        string $description,
        string $recipient,
        ?string $actionLink = null
    ): Event {
        $event = $this->eventFactory->createInstance(
            domain: Message::DOMAIN_NAME,
            title: $title,
            description: $description,
            recipient: $recipient,
            actionLabel: null !== $actionLink ? 'En savoir plus' : null,
            actionLink: $actionLink,
            entityName: Signalement::class,
            entityUuid: $messageThread->getSignalement()->getUuid()
        );

        $this->save($event);

        return $event;
    }
}