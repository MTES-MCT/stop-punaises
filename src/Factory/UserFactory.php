<?php

namespace App\Factory;

use App\Entity\Enum\Role;
use App\Entity\Enum\Status;
use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFactory
{
    public function __construct(
        private UserPasswordHasherInterface $hasher)
    {
    }

    public function createInstanceFrom(Role $role, string $email): User
    {
        $user = new User();
        $user->setStatus(Status::INACTIVE)
            ->setEmail($email)
            ->addRole($role->value)
            ->setActive(true);
        $password = $this->hasher->hashPassword($user, 'punaise');
        $user->setPassword($password);

        return $user;
    }
}
