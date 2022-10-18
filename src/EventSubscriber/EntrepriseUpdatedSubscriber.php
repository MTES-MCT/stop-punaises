<?php

namespace App\EventSubscriber;

use App\Entity\Enum\Role;
use App\Entity\User;
use App\Event\EntrepriseUpdatedEvent;
use App\Manager\UserManager;
use App\Service\Mailer\MailerProviderInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Security;

class EntrepriseUpdatedSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private MailerProviderInterface $mailerProvider,
        private UserManager $userManager,
        private Security $security,
        private UrlGeneratorInterface $urlGenerator,
        private RequestStack $requestStack,
    ) {
    }

    public function onEntrepriseUpdatedEvent(EntrepriseUpdatedEvent $event): void
    {
        $user = $this->userManager->updateEmailFrom($event->getEntreprise(), $event->getCurrentEmail());

        if ($user instanceof User) {
            $this->mailerProvider->sendActivateMessage($user);

            if (!$this->security->isGranted(Role::ROLE_ADMIN->value)) {
                $session = $this->requestStack->getSession();
                $session->getFlashBag()->add('success', 'Merci d\'activer votre compte');
                $response = new RedirectResponse($this->urlGenerator->generate('app_logout'));
                $response->send();
            }
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EntrepriseUpdatedEvent::NAME => 'onEntrepriseUpdatedEvent',
        ];
    }
}
