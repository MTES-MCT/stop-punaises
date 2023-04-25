<?php

namespace App\EventSubscriber;

use App\Event\InterventionEstimationSentEvent;
use App\Service\Event\EstimationSentFactory;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class InterventionEstimationSentSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private EstimationSentFactory $estimationSentFactory,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            InterventionEstimationSentEvent::NAME => 'onInterventionEstimationSent',
        ];
    }

    public function onInterventionEstimationSent(InterventionEstimationSentEvent $interventionEstimationSentEvent)
    {
        $this->estimationSentFactory->add(
            $interventionEstimationSentEvent->getIntervention(),
            $interventionEstimationSentEvent->getCreatedAt(),
            $interventionEstimationSentEvent->getUserId(),
        );
    }
}
