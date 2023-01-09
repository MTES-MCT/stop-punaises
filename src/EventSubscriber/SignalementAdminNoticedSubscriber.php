<?php

namespace App\EventSubscriber;

use App\Entity\Event;
use App\Event\SignalementAdminNoticedEvent;
use App\Manager\EventManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SignalementAdminNoticedSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private EventManager $eventManager,
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
        $this->eventManager->createEventAdminNotice(
            signalement: $signalement,
            description: 'Vous avez indiqué que le problème de punaises n\'est pas résolu. L\'administrateur va vous contacter.',
            recipient: $signalement->getEmailOccupant(),
            userId: null,
        );

        // Event pour l'admin
        $this->eventManager->createEventAdminNotice(
            signalement: $signalement,
            description: 'L\'usager a indiqué que le problème de punaises n\'est pas résolu. L\'administrateur va le contacter.',
            recipient: null,
            userId: Event::USER_ADMIN,
        );

        // Event pour l'entreprise qui a traité le problème
        $userId = null;
        $interventions = $signalement->getInterventions();
        foreach ($interventions as $intervention) {
            if ($intervention->isAcceptedByUsager() && $intervention->getResolvedByEntrepriseAt()) {
                $userId = $intervention->getEntreprise()->getId();
                break;
            }
        }
        $this->eventManager->createEventAdminNotice(
            signalement: $signalement,
            description: 'L\'usager a indiqué que le problème de punaises n\'est pas résolu. L\'administrateur va le contacter.',
            recipient: null,
            userId: $userId,
        );
    }
}
