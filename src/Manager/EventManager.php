<?php

namespace App\Manager;

use App\Entity\Event;
use App\Entity\MessageThread;
use App\Entity\Signalement;
use App\Factory\EventFactory;
use App\Repository\EventRepository;
use Doctrine\Persistence\ManagerRegistry;

class EventManager extends AbstractManager
{
    public function __construct(private EventFactory $eventFactory,
                                private EventRepository $eventRepository,
                                protected ManagerRegistry $managerRegistry,
                                protected string $entityName = Event::class
    ) {
        parent::__construct($managerRegistry, $entityName);
    }

    public function setPreviousInactive(
        Signalement $signalement,
        string $domain,
        ?int $userId,
        ?string $recipient,
    ) {
        $activeEvents = $this->eventRepository->findActiveDomainEvents(
            $signalement->getUuid(),
            $domain,
            $userId,
            $recipient
        );

        foreach ($activeEvents as $activeEvent) {
            $activeEvent->setActive(false);
            $this->save($activeEvent);
        }
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
        $this->setPreviousInactive($messageThread->getSignalement(), Event::DOMAIN_MESSAGE, $userId, $recipient);

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

    public function createEventEstimationsAllRefused(
        Signalement $signalement,
        string $description,
        ?string $recipient,
        ?int $userId,
        ?string $actionLabel,
        ?string $actionLink,
    ): Event {
        $this->setPreviousInactive($signalement, Event::DOMAIN_ESTIMATIONS_ALL_REFUSED, $userId, $recipient);

        $event = $this->eventFactory->createInstance(
            domain: Event::DOMAIN_ESTIMATIONS_ALL_REFUSED,
            title: 'Plus d\'estimation disponible',
            description: $description,
            userId: $userId,
            recipient: $recipient,
            actionLabel: $actionLabel,
            actionLink: $actionLink,
            entityName: Signalement::class,
            entityUuid: $signalement->getUuid()
        );

        $this->save($event);

        return $event;
    }

    public function createEventNoEntrepriseAvailable(
        Signalement $signalement,
        string $description,
        ?string $recipient,
        ?int $userId,
        ?string $actionLabel,
        ?string $actionLink,
    ): Event {
        $this->setPreviousInactive($signalement, Event::DOMAIN_NO_ENTREPRISE_AVAILABLE, $userId, $recipient);

        $event = $this->eventFactory->createInstance(
            domain: Event::DOMAIN_NO_ENTREPRISE_AVAILABLE,
            title: 'Aucune entreprise disponible',
            description: $description,
            userId: $userId,
            recipient: $recipient,
            actionLabel: $actionLabel,
            actionLink: $actionLink,
            entityName: Signalement::class,
            entityUuid: $signalement->getUuid()
        );

        $this->save($event);

        return $event;
    }

    public function createEventReminderAutotraitement(
        Signalement $signalement,
        string $description,
        ?string $recipient,
        ?int $userId,
        ?string $label,
        ?string $actionLabel,
        ?string $modalToOpen,
    ): Event {
        $this->setPreviousInactive($signalement, Event::DOMAIN_REMINDER_AUTOTRAITEMENT, $userId, $recipient);

        $event = $this->eventFactory->createInstance(
            domain: Event::DOMAIN_REMINDER_AUTOTRAITEMENT,
            title: 'Suivi du traitement',
            description: $description,
            userId: $userId,
            recipient: $recipient,
            label: $label,
            actionLabel: $actionLabel,
            actionLink: 'modalToOpen:'.$modalToOpen,
            entityName: Signalement::class,
            entityUuid: $signalement->getUuid()
        );

        $this->save($event);

        return $event;
    }

    public function createEventReminderPro(
        Signalement $signalement,
        string $description,
        ?string $recipient,
        ?int $userId,
        ?string $label,
        ?string $actionLabel,
        ?string $modalToOpen,
    ): Event {
        $this->setPreviousInactive($signalement, Event::DOMAIN_REMINDER_PRO, $userId, $recipient);

        $event = $this->eventFactory->createInstance(
            domain: Event::DOMAIN_REMINDER_PRO,
            title: 'Suivi du traitement',
            description: $description,
            userId: $userId,
            recipient: $recipient,
            label: $label,
            actionLabel: $actionLabel,
            actionLink: 'modalToOpen:'.$modalToOpen,
            entityName: Signalement::class,
            entityUuid: $signalement->getUuid()
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
