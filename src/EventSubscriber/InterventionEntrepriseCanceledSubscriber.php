<?php

namespace App\EventSubscriber;

use App\Entity\Event;
use App\Event\InterventionEntrepriseCanceledEvent;
use App\Manager\EventManager;
use App\Manager\InterventionManager;
use App\Repository\EventRepository;
use App\Repository\InterventionRepository;
use App\Service\Event\EstimationSentFactory;
use App\Service\Mailer\MailerProvider;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class InterventionEntrepriseCanceledSubscriber implements EventSubscriberInterface
{
    private const LABEL_CANCELED = 'Intervention annulée';

    public function __construct(
        private EventManager $eventManager,
        private EventRepository $eventRepository,
        private MailerProvider $mailerProvider,
        private InterventionRepository $interventionRepository,
        private InterventionManager $interventionManager,
        private EstimationSentFactory $estimationSentFactory,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            InterventionEntrepriseCanceledEvent::NAME => 'onInterventionEntrepriseCanceled',
        ];
    }

    public function onInterventionEntrepriseCanceled(InterventionEntrepriseCanceledEvent $interventionEntrepriseCanceledEvent)
    {
        $intervention = $interventionEntrepriseCanceledEvent->getIntervention();
        $signalement = $intervention->getSignalement();

        // Send mail to usager
        $this->mailerProvider->sendInterventionCanceled(
            $intervention->getSignalement(),
            $intervention->getEntreprise()->getNom()
        );

        // Add event to signalement
        $event = $this->eventManager->createEventEstimationSent(
            signalement: $signalement,
            title: 'Estimation '.$intervention->getEntreprise()->getNom(),
            description: 'L\'entreprise '.$intervention->getEntreprise()->getNom().' a annulé son intervention',
            recipient: null,
            userId: Event::USER_ADMIN,
            userIdExcluded: null,
            label: self::LABEL_CANCELED,
            actionLabel: 'En savoir plus',
            actionLink: 'modalToOpen:view-estimation-'.$intervention->getId(),
        );
        $this->eventRepository->updateCreatedAt($event, $interventionEntrepriseCanceledEvent->getCreatedAt());

        $event = $this->eventManager->createEventEstimationSent(
            signalement: $signalement,
            title: 'Estimation '.$intervention->getEntreprise()->getNom(),
            description: 'L\'entreprise sélectionnée a annulé son intervention',
            recipient: null,
            userId: Event::USER_ALL,
            userIdExcluded: $intervention->getEntreprise()->getUser()->getId(),
            label: self::LABEL_CANCELED,
            actionLabel: null,
            actionLink: null,
        );
        $this->eventRepository->updateCreatedAt($event, $interventionEntrepriseCanceledEvent->getCreatedAt());

        $event = $this->eventManager->createEventEstimationSent(
            signalement: $signalement,
            title: 'Estimation '.$intervention->getEntreprise()->getNom(),
            description: 'Vous avez annulé votre intervention.',
            recipient: null,
            userId: $intervention->getEntreprise()->getUser()->getId(),
            userIdExcluded: null,
            label: self::LABEL_CANCELED,
            actionLabel: 'En savoir plus',
            actionLink: 'modalToOpen:view-estimation-'.$intervention->getId(),
        );
        $this->eventRepository->updateCreatedAt($event, $interventionEntrepriseCanceledEvent->getCreatedAt());

        $event = $this->eventManager->createEventEstimationSent(
            signalement: $signalement,
            title: 'Estimation '.$intervention->getEntreprise()->getNom(),
            description: 'L\'entreprise '.$intervention->getEntreprise()->getNom().' a annulé son intervention',
            recipient: $intervention->getSignalement()->getEmailOccupant(),
            userId: null,
            userIdExcluded: null,
            label: self::LABEL_CANCELED,
            actionLabel: 'En savoir plus',
            actionLink: 'modalToOpen:estimation-accepted-'.$intervention->getId(),
        );
        $this->eventRepository->updateCreatedAt($event, $interventionEntrepriseCanceledEvent->getCreatedAt());

        // Re-open interventions if accepted by entreprise, not canceled by entreprise, refused by usager
        $reopenInterventions = $this->interventionRepository->findBy([
            'signalement' => $signalement,
            'accepted' => true,
            'acceptedByUsager' => false,
            'canceledByEntrepriseAt' => null,
        ]);
        foreach ($reopenInterventions as $reopenIntervention) {
            $reopenIntervention->setAcceptedByUsager(null);
            $reopenIntervention->setChoiceByUsagerAt(null);
            $this->interventionManager->save($reopenIntervention);

            $this->estimationSentFactory->add(
                $reopenIntervention,
                $interventionEntrepriseCanceledEvent->getCreatedAt(),
                $reopenIntervention->getEntreprise()->getUser()->getId(),
            );
        }
    }
}
