<?php

namespace App\EventSubscriber;

use App\Entity\Event;
use App\Event\InterventionUsagerRefusedEvent;
use App\Manager\EventManager;
use App\Repository\EventRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class InterventionUsagerRefusedSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private EventManager $eventManager,
        private EventRepository $eventRepository,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            InterventionUsagerRefusedEvent::NAME => 'onInterventionUsagerRefused',
        ];
    }

    public function onInterventionUsagerRefused(InterventionUsagerRefusedEvent $interventionUsagerRefusedEvent)
    {
        $intervention = $interventionUsagerRefusedEvent->getIntervention();

        $signalement = $intervention->getSignalement();
        $event = $this->eventManager->createEventEstimationSent(
            signalement: $signalement,
            title: 'Estimation '.$intervention->getEntreprise()->getNom(),
            description: 'L\'entreprise '.$intervention->getEntreprise()->getNom().' a envoyé une estimation',
            recipient: null,
            userId: Event::USER_ADMIN,
            userIdExcluded: null,
            label: 'Estimation refusée',
            actionLabel: 'En savoir plus',
            actionLink: 'modalToOpen:view-estimation-'.$intervention->getId(),
        );
        $this->eventRepository->updateCreatedAt($event, $interventionUsagerRefusedEvent->getCreatedAt());

        $event = $this->eventManager->createEventEstimationSent(
            signalement: $signalement,
            title: 'Estimation '.$intervention->getEntreprise()->getNom(),
            description: 'Vous avez envoyé une estimation à l\'usager.',
            recipient: null,
            userId: $intervention->getEntreprise()->getUser()->getId(),
            userIdExcluded: null,
            label: 'Estimation refusée',
            actionLabel: 'En savoir plus',
            actionLink: 'modalToOpen:view-estimation-'.$intervention->getId(),
        );
        $this->eventRepository->updateCreatedAt($event, $interventionUsagerRefusedEvent->getCreatedAt());

        $event = $this->eventManager->createEventEstimationSent(
            signalement: $signalement,
            title: 'Estimation '.$intervention->getEntreprise()->getNom(),
            description: 'L\'entreprise '.$intervention->getEntreprise()->getNom().' vous a envoyé une estimation',
            recipient: $intervention->getSignalement()->getEmailOccupant(),
            userId: null,
            userIdExcluded: null,
            label: 'Estimation refusée',
            actionLabel: null,
            actionLink: null,
        );
        $this->eventRepository->updateCreatedAt($event, $interventionUsagerRefusedEvent->getCreatedAt());

        // On considère que toutes les interventions ne sont pas encore refusées si
        // - il en reste sans estimation
        // - il en reste sans que l'usager n'ait répondu
        // - il en reste où l'usager a répondu positivement
        $isAllRefusedEstimations = true;
        $interventions = $intervention->getSignalement()->getInterventions();
        if (\count($interventions) > 0) {
            foreach ($interventions as $intervention) {
                if ($intervention->isAccepted()) {
                    if (!$intervention->getEstimationSentAt()) {
                        $isAllRefusedEstimations = false;
                        break;
                    } elseif (!$intervention->getChoiceByUsagerAt()) {
                        $isAllRefusedEstimations = false;
                        break;
                    } elseif ($intervention->isAcceptedByUsager()) {
                        $isAllRefusedEstimations = false;
                        break;
                    }
                }
            }
        }
        if ($isAllRefusedEstimations) {
            $this->eventManager->createEventEstimationsAllRefused(
                signalement: $intervention->getSignalement(),
                description: 'L\'usager a refusé toutes les estimations des entreprises',
                recipient: null,
                userId: Event::USER_ADMIN,
                actionLabel: null,
                actionLink: null,
            );
            $this->eventManager->createEventEstimationsAllRefused(
                signalement: $intervention->getSignalement(),
                description: 'Vous avez refusé toutes les estimations des entreprises',
                recipient: $intervention->getSignalement()->getEmailOccupant(),
                userId: null,
                actionLabel: 'En savoir plus',
                actionLink: 'modalToOpen:empty-estimations',
            );
        }
    }
}
