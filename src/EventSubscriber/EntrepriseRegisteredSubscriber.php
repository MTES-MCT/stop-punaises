<?php

namespace App\EventSubscriber;

use App\Entity\Enum\Role;
use App\Event\EntrepriseRegisteredEvent;
use App\Manager\UserManager;
use App\Service\Mailer\MailerProvider;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EntrepriseRegisteredSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private MailerProvider $mailerProvider,
        private UserManager $userManager,
    ) {
    }

    public function onEntrepriseRegisteredEvent(EntrepriseRegisteredEvent $event): void
    {
        $user = $this->userManager->createFrom($event->getEntreprise(), Role::ROLE_ENTREPRISE);
        $this->mailerProvider->sendActivateMessage($user);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EntrepriseRegisteredEvent::NAME => 'onEntrepriseRegisteredEvent',
        ];
    }
}
