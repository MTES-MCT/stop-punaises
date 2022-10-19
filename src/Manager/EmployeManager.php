<?php

namespace App\Manager;

use App\Entity\Employe;
use Doctrine\Persistence\ManagerRegistry;

class EmployeManager extends AbstractManager
{
    public function __construct(ManagerRegistry $managerRegistry, string $entityName = Employe::class)
    {
        $this->managerRegistry = $managerRegistry;
        $this->entityName = $entityName;
    }
}
