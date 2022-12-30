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

    public function createEventAdminNotice(Signalement $signalement, string $recipient): Event
    {
        $event = $this->eventFactory->createInstance(
            domain: Event::DOMAIN_ADMIN_NOTICE,
            title: 'Infestation non résolue',
            description: 'L\'usager a indiqué que le problème de punaises n\'est pas résolu. L\'administrateur va le contacter.',
            userId: null,
            recipient: $recipient,
            entityName: Signalement::class,
            entityUuid: $signalement->getUuid()
        );

        $this->save($event);

        return $event;
    }
}
