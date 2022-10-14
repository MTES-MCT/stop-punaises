<?php

namespace App\Factory;

use App\Entity\Enum\Role;
use App\Entity\Enum\Status;
use App\Entity\User;
use App\Service\Token\GeneratorToken;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFactory
{
    public function __construct(
        private UserPasswordHasherInterface $hasher,
        private GeneratorToken $token
    ) {
    }

    public function createInstanceFrom(Role $role, string $email): User
    {
        $user = (new User())
            ->setStatus(Status::INACTIVE)
            ->setEmail($email)
            ->addRole($role->value)
            ->setActive(true);
        $password = $this->hasher->hashPassword($user, $this->token->generateToken());
        $user->setPassword($password);

        return $user;
    }
}
