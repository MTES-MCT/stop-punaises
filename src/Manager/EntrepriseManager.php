<?php

namespace App\Manager;

use App\Entity\Signalement;
use App\Repository\EntrepriseRepository;
use App\Repository\InterventionRepository;
use Doctrine\Persistence\ManagerRegistry;

class EntrepriseManager extends AbstractManager
{
    public function __construct(
        protected ManagerRegistry $managerRegistry,
        protected EntrepriseRepository $entrepriseRepository,
        protected InterventionRepository $interventionRepository,
        protected string $entityName = Signalement::class)
    {
        parent::__construct($managerRegistry, $entityName);
    }

    public function isEntrepriseRemainingForSignalement(Signalement $signalement)
    {
        $remainingEntreprises = false;
        $entreprises = $this->entrepriseRepository->findByTerritoire($signalement->getTerritoire());
        foreach ($entreprises as $entreprise) {
            $entrepriseIntervention = $this->interventionRepository->findBySignalementAndEntreprise(
                $signalement,
                $entreprise
            );
            if (!$entrepriseIntervention || $entrepriseIntervention->isAccepted()) {
                $remainingEntreprises = true;
                break;
            }
        }

        return $remainingEntreprises;
    }
}
