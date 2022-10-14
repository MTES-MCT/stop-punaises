<?php

namespace App\EventSubscriber;

use App\Event\EntrepriseRegisteredEvent;
use App\Event\EntrepriseUpdatedEvent;
use App\Manager\UserManager;
use App\Service\Mailer\MailerProviderInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EntrepriseUpdatedSubscriber implements EventSubscriberInterface
{
    public function __construct(private MailerProviderInterface $mailerProvider,
                                private UserManager $userManager
    ) {
    }

    public function onEntrepriseUpdatedEvent(EntrepriseUpdatedEvent $event): void
    {
        $user = $this->userManager->updateEmailFrom($event->getEntreprise(), $event->getCurrentEmail());
        $this->mailerProvider->sendActivateMessage($user);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EntrepriseRegisteredEvent::NAME => 'onEntrepriseUpdatedEvent',
        ];
    }
}
