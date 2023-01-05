<?php

namespace App\Manager;

use App\Entity\Event;
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

    public function createEventNewSignalement(
        Signalement $signalement,
        string $description,
        ?string $recipient,
        ?int $userId
    ): Event {
        $event = $this->eventFactory->createInstance(
            domain: Event::DOMAIN_NEW_SIGNALEMENT,
            title: 'Signalement déposé',
            description: $description,
            userId: $userId,
            recipient: $recipient,
            entityName: Signalement::class,
            entityUuid: $signalement->getUuid()
        );

        $this->save($event);

        return $event;
    }

    public function createEventProtocole(
        Signalement $signalement,
        ?string $recipient,
        ?int $userId,
        ?string $pdfUrl,
    ): Event {
        $event = $this->eventFactory->createInstance(
            domain: Event::DOMAIN_PROTOCOLE,
            title: 'Protocole envoyé',
            description: 'Le protocole d\'auto traitement a bien été envoyé.',
            userId: $userId,
            recipient: $recipient,
            actionLink: $pdfUrl ? $pdfUrl : null,
            actionLabel: $pdfUrl ? 'Télécharger le protocole' : null,
            entityName: Signalement::class,
            entityUuid: $signalement->getUuid()
        );

        $this->save($event);

        return $event;
    }

    public function createEventSwitchTraitement(
        Signalement $signalement,
        string $description,
        ?string $recipient,
        ?int $userId
    ): Event {
        $event = $this->eventFactory->createInstance(
            domain: Event::DOMAIN_SWITCH_TRAITEMENT,
            title: 'Signalement transféré',
            description: $description,
            userId: $userId,
            recipient: $recipient,
            entityName: Signalement::class,
            entityUuid: $signalement->getUuid()
        );

        $this->save($event);

        return $event;
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
            domain: Event::DOMAIN_MESSAGE,
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

    public function createEventAdminNotice(
        Signalement $signalement,
        string $description,
        ?string $recipient,
        ?int $userId
    ): Event {
        $event = $this->eventFactory->createInstance(
            domain: Event::DOMAIN_ADMIN_NOTICE,
            title: 'Infestation non résolue',
            description: $description,
            userId: $userId,
            recipient: $recipient,
            entityName: Signalement::class,
            entityUuid: $signalement->getUuid()
        );

        $this->save($event);

        return $event;
    }

    public function createEventResolveSignalement(
        Signalement $signalement,
        string $description,
        ?string $recipient,
        ?int $userId
    ): Event {
        $event = $this->eventFactory->createInstance(
            domain: Event::DOMAIN_RESOLVE_SIGNALEMENT,
            title: 'Problème résolu',
            description: $description,
            userId: $userId,
            recipient: $recipient,
            entityName: Signalement::class,
            entityUuid: $signalement->getUuid()
        );

        $this->save($event);

        return $event;
    }

    public function createEventCloseSignalement(
        Signalement $signalement,
        string $description,
        ?string $recipient,
        ?int $userId
    ): Event {
        $event = $this->eventFactory->createInstance(
            domain: Event::DOMAIN_CLOSE_SIGNALEMENT,
            title: 'Signalement terminé',
            description: $description,
            userId: $userId,
            recipient: $recipient,
            entityName: Signalement::class,
            entityUuid: $signalement->getUuid()
        );

        $this->save($event);

        return $event;
    }
}
