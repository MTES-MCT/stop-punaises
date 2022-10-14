<?php

namespace App\Factory;

use App\Entity\Enum\Role;
use App\Entity\Enum\Status;
use App\Entity\User;

class UserFactory
{
    public function createInstanceFrom(Role $role, string $email): User
    {
        return (new User())
            ->setStatus(Status::INACTIVE)
            ->setEmail($email)
            ->addRole($role->value)
            ->setActive(true);
    }
}
