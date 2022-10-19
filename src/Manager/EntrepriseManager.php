<?php

namespace App\Manager;

use App\Entity\Entreprise;
use Doctrine\Persistence\ManagerRegistry;

class EntrepriseManager extends AbstractManager
{
    public function __construct(ManagerRegistry $managerRegistry, string $entityName = Entreprise::class)
    {
        $this->managerRegistry = $managerRegistry;
        $this->entityName = $entityName;
    }
}
