<?php

namespace App\EventSubscriber;

use App\Entity\Event;
use App\Event\SignalementAdminNoticedEvent;
use App\Manager\EventManager;
use App\Repository\EventRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SignalementAdminNoticedSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private EventManager $eventManager,
        private EventRepository $eventRepository,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SignalementAdminNoticedEvent::NAME => 'onSignalementAdminNoticed',
        ];
    }

    public function onSignalementAdminNoticed(SignalementAdminNoticedEvent $signalementAdminNoticedEvent)
    {
        $signalement = $signalementAdminNoticedEvent->getSignalement();

        // Event pour l'usager
        $event = $this->eventManager->createEventAdminNotice(
            signalement: $signalement,
            description: 'Vous avez indiqué que le problème de punaises n\'est pas résolu. L\'administrateur va vous contacter.',
            recipient: $signalement->getEmailOccupant(),
            userId: null,
        );
        $this->eventRepository->updateCreatedAt($event, $signalementAdminNoticedEvent->getCreatedAt());

        // Event pour l'admin
        $event = $this->eventManager->createEventAdminNotice(
            signalement: $signalement,
            description: 'L\'usager a indiqué que le problème de punaises n\'est pas résolu. L\'administrateur va le contacter.',
            recipient: null,
            userId: Event::USER_ADMIN,
        );
        $this->eventRepository->updateCreatedAt($event, $signalementAdminNoticedEvent->getCreatedAt());

        // Event pour l'entreprise qui a traité le problème
        $userId = null;
        $interventions = $signalement->getInterventions();
        foreach ($interventions as $intervention) {
            if ($intervention->isAcceptedByUsager() && $intervention->getResolvedByEntrepriseAt()) {
                $userId = $intervention->getEntreprise()->getId();
                break;
            }
        }
        $event = $this->eventManager->createEventAdminNotice(
            signalement: $signalement,
            description: 'L\'usager a indiqué que le problème de punaises n\'est pas résolu. L\'administrateur va le contacter.',
            recipient: null,
            userId: $userId,
        );
        $this->eventRepository->updateCreatedAt($event, $signalementAdminNoticedEvent->getCreatedAt());
    }
}
