<?php

namespace App\Manager;

use App\Entity\Enum\Declarant;
use App\Entity\Enum\Role;
use App\Entity\Signalement;
use App\Repository\SignalementRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Security;

class SignalementManager extends AbstractManager
{
    public function __construct(
        protected ManagerRegistry $managerRegistry,
        private SignalementRepository $signalementRepository,
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

    public function findHistoriqueEntreprise(): ?array
    {
        $parameters = ['declarant' => Declarant::DECLARANT_ENTREPRISE];
        if (!$this->security->isGranted(Role::ROLE_ADMIN->value)) {
            $parameters['entreprise'] = $this->security->getUser()->getEntreprise();
        }

        return $this->findBy($parameters);
    }

    public function findDeclaredByOccupants(): ?array
    {
        return $this->security->isGranted(Role::ROLE_ADMIN->value)
        ? $this->signalementRepository->findDeclaredByOccupants()
        : $this->signalementRepository->findDeclaredByOccupants($this->security->getUser()->getEntreprise());
    }
}
