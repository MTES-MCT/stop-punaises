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
    public function __construct(private EventFactory $eventFactory,
                                protected ManagerRegistry $managerRegistry,
                                protected string $entityName = Event::class
    ) {
        parent::__construct($managerRegistry, $entityName);
    }

    public function createEventMessage(
        MessageThread $messageThread,
        string $title,
        string $description,
        string $recipient,
        ?int $userId = null,
        ?string $actionLink = null
    ): Event {
        $event = $this->eventFactory->createInstance(
            domain: Message::DOMAIN_NAME,
            title: $title,
            description: $description,
            userId: $userId,
            recipient: $recipient,
            actionLink: $actionLink,
            actionLabel: null !== $actionLink ? 'En savoir plus' : null,
            entityName: Signalement::class,
            entityUuid: $messageThread->getSignalement()->getUuid()
        );

        $this->save($event);

        return $event;
    }
}
