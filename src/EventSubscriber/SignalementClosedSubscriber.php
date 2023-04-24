<?php

namespace App\EventSubscriber;

use App\Entity\Event;
use App\Event\SignalementClosedEvent;
use App\Manager\EventManager;
use App\Repository\EventRepository;
use App\Repository\InterventionRepository;
use App\Service\Mailer\MailerProvider;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SignalementClosedSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private EventManager $eventManager,
        private EventRepository $eventRepository,
        private InterventionRepository $interventionRepository,
        private MailerProvider $mailerProvider,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SignalementClosedEvent::NAME => 'onSignalementClosed',
        ];
    }

    public function onSignalementClosed(SignalementClosedEvent $signalementClosedEvent)
    {
        $signalement = $signalementClosedEvent->getSignalement();

        // Notice for entreprises which are currently following the signalement
        $acceptedInterventions = $this->interventionRepository->findBy([
            'signalement' => $signalement,
            'accepted' => true,
        ]);
        foreach ($acceptedInterventions as $intervention) {
            if (!$intervention->getChoiceByUsagerAt() || $intervention->isAcceptedByUsager()) {
                $this->mailerProvider->sendSignalementClosed($intervention->getEntreprise()->getUser()->getEmail(), $intervention->getSignalement());
            }
        }

        $event = $this->eventManager->createEventCloseSignalement(
            signalement: $signalement,
            description: 'L\'usager a mis fin à la procédure',
            recipient: null,
            userId: Event::USER_ALL,
        );
        $this->eventRepository->updateCreatedAt($event, $signalementClosedEvent->getCreatedAt());

        $event = $this->eventManager->createEventCloseSignalement(
            signalement: $signalement,
            description: 'Vous avez mis fin à la procédure. Merci d\'avoir utilisé Stop Punaises.',
            recipient: $signalement->getEmailOccupant(),
            userId: null,
        );
        $this->eventRepository->updateCreatedAt($event, $signalementClosedEvent->getCreatedAt());
    }
}
