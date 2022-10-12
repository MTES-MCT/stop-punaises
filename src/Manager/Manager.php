<?php

namespace App\Manager;

use Doctrine\Persistence\ManagerRegistry;

class Manager extends AbstractManager
{
    public function __construct(protected ManagerRegistry $managerRegistry, protected string $entityName = '')
    {
        parent::__construct($managerRegistry, $entityName);
    }
}
