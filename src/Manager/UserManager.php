<?php

namespace App\Manager;

use App\Entity\User;
use App\Service\Mailer\MailerProviderInterface;
use App\Service\Mailer\MessageFactory;
use App\Service\Mailer\Template;
use App\Service\ResetPasswordToken;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class UserManager extends AbstractManager
{
    public function __construct(
        protected ManagerRegistry $managerRegistry,
        private ParameterBagInterface $parameterBag,
        private MailerProviderInterface $mailerProvider,
        private MessageFactory $messageFactory,
        private ResetPasswordToken $tokenGenerator,
        private PasswordHasherFactoryInterface $passwordHasherFactory,
        private UrlGeneratorInterface $urlGenerator,
        string $entityName = User::class)
    {
        parent::__construct($managerRegistry, $entityName);
    }

    public function requestPasswordFrom(string $email): void
    {
        /** @var User $user */
        $user = $this->findOneBy(['email' => $email]);
        $user->setPasswordExpiredAt(
            (new \DateTime())->modify($this->parameterBag->get('confirmation_token_lifetime'))
        );
        $token = $this->tokenGenerator->generateToken();
        $user->setConfirmationToken($token);
        $this->save($user);

        $resetPasswordLink = $this->urlGenerator->generate('reset_password', ['confirmation_token' => $token]);
        $this->mailerProvider->send(
            $this
                ->messageFactory
                ->createInstanceFrom(Template::RESET_PASSWORD, ['link' => $resetPasswordLink])
                ->setTo([$user->getEmail()])
        );
    }

    public function resetPassword(User $user, string $password): User
    {
        $password = $this->passwordHasherFactory->getPasswordHasher($user)->hash($password);
        $user
            ->setPassword($password)
            ->setConfirmationToken(null)
            ->setPasswordExpiredAt(null);

        $this->save($user);

        return $user;
    }
}
