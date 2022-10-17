<?php

namespace App\Manager;

use App\Entity\Enum\Role;
use App\Entity\Signalement;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Security;

class SignalementManager extends AbstractManager
{
    public function __construct(
        protected ManagerRegistry $managerRegistry,
        private Security $security,
        protected string $entityName = Signalement::class)
    {
        parent::__construct($managerRegistry, $entityName);
    }

    public function findByPrivileges(): ?array
    {
        return $this->security->isGranted(Role::ROLE_ADMIN->value)
        ? $this->findAll()
        : $this->findBy(['entreprise' => $this->security->getUser()->getEntreprise()]);
    }
}
