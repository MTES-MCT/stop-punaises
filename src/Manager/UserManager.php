<?php

namespace App\Manager;

use App\Entity\Entreprise;
use App\Entity\Enum\Role;
use App\Entity\Enum\Status;
use App\Entity\User;
use App\Exception\User\RequestPasswordNotAllowedException;
use App\Exception\User\UserAccountAlreadyActiveException;
use App\Exception\User\UserEmailNotFoundException;
use App\Factory\UserFactory;
use App\Service\Mailer\MailerProviderInterface;
use App\Service\Mailer\MessageFactory;
use App\Service\Token\GeneratorToken;
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
        private GeneratorToken $tokenGenerator,
        private PasswordHasherFactoryInterface $passwordHasherFactory,
        private UrlGeneratorInterface $urlGenerator,
        private UserFactory $userFactory,
        string $entityName = User::class)
    {
        parent::__construct($managerRegistry, $entityName);
    }

    public function requestPasswordFrom(string $email): void
    {
        $user = $this->loadUserToken($email);
        if (Status::INACTIVE === $user->getStatus()) {
            throw new RequestPasswordNotAllowedException($email);
        }
        $this->save($user);
        $this->mailerProvider->sendResetPasswordMessage($user);
    }

    public function requestActivationFrom(string $email)
    {
        $user = $this->loadUserToken($email);
        if (Status::ACTIVE === $user->getStatus()) {
            throw new UserAccountAlreadyActiveException($email);
        }
        $this->save($user);
        $this->mailerProvider->sendActivateMessage($user);
    }

    public function resetPassword(User $user, string $password): User
    {
        $password = $this->passwordHasherFactory->getPasswordHasher($user)->hash($password);
        $user
            ->setPassword($password)
            ->setToken(null)
            ->setStatus(Status::ACTIVE)
            ->setTokenExpiredAt(null);

        $this->save($user);

        return $user;
    }

    public function createFrom(Entreprise $entreprise, Role $role): User
    {
        $user = $this->userFactory->createInstanceFrom($role, $entreprise->getEmail());
        $user
            ->setToken($this->tokenGenerator->generateToken())
            ->setTokenExpiredAt(
                (new \DateTimeImmutable())->modify($this->parameterBag->get('token_lifetime'))
            )
            ->setEntreprise($entreprise);
        $this->save($user);

        return $user;
    }

    public function updateEmailFrom(Entreprise $entreprise, string $currentEmail): User
    {
        /** @var User $user */
        $user = $this->findOneBy(['email' => $currentEmail]);
        $user = $this->loadUserToken($user->getEmail());

        $user->setEmail($entreprise->getEmail());
        $user->setStatus(Status::INACTIVE);
        $this->save($user);

        return $user;
    }

    private function loadUserToken(string $email): User
    {
        /** @var User $user */
        $user = $this->findOneBy(['email' => $email]);
        if (null === $user) {
            throw new UserEmailNotFoundException($email);
        }
        $user
            ->setToken($this->tokenGenerator->generateToken())
            ->setTokenExpiredAt(
                (new \DateTimeImmutable())->modify($this->parameterBag->get('token_lifetime'))
            );

        return $user;
    }
}
