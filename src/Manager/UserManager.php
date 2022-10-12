<?php

namespace App\Manager;

use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;

class UserManager extends AbstractManager
{
    public function __construct(ManagerRegistry $managerRegistry, string $entityName = User::class)
    {
        parent::__construct($managerRegistry, $entityName);
    }
}
