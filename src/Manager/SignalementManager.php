<?php

namespace App\Manager;

use App\Dto\SignalementOccupantDataTableFilters;
use App\Entity\Enum\Declarant;
use App\Entity\Enum\Role;
use App\Entity\Signalement;
use App\Entity\User;
use App\Factory\SignalementFactory;
use App\Repository\SignalementRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\SecurityBundle\Security;

class SignalementManager extends AbstractManager
{
    public function __construct(
        protected ManagerRegistry $managerRegistry,
        private SignalementRepository $signalementRepository,
        private Security $security,
        private SignalementFactory $signalementFactory,
        protected string $entityName = Signalement::class,
    ) {
        parent::__construct($managerRegistry, $entityName);
    }

    public function findByPrivileges(): ?array
    {
        /** @var User $user */
        $user = $this->security->getUser();

        return $this->security->isGranted(Role::ROLE_ADMIN->value)
        ? $this->findAll()
        : $this->findBy(['entreprise' => $user->getEntreprise()]);
    }

    public function findHistoriqueEntreprise(): ?array
    {
        /** @var User $user */
        $user = $this->security->getUser();
        $parameters = ['declarant' => Declarant::DECLARANT_ENTREPRISE];
        if (!$this->security->isGranted(Role::ROLE_ADMIN->value)) {
            $parameters['entreprise'] = $user->getEntreprise();
        }

        return $this->findBy($parameters);
    }

    public function findDeclaredByOccupants(
        ?string $start = null,
        ?string $length = null,
        ?string $orderColumn = null,
        ?string $orderDirection = null,
        ?SignalementOccupantDataTableFilters $filters = null,
    ): array|int {
        /** @var User $user */
        $user = $this->security->getUser();
        $entreprise = $this->security->isGranted(Role::ROLE_ADMIN->value) ? null : $user->getEntreprise();

        return $this->signalementRepository->findDeclaredByOccupants(
            entreprise: $entreprise,
            start: $start,
            length: $length,
            orderColumn: $orderColumn,
            orderDirection: $orderDirection,
            filters: $filters
        );
    }

    public function countSignalements(): ?array
    {
        /** @var User $user */
        $user = $this->security->getUser();
        $result = [];
        if ($this->security->isGranted(Role::ROLE_ADMIN->value)) {
            $result[0] = $this->signalementRepository->countOpenWithoutIntervention();
            $result[1] = $this->signalementRepository->countOpenWithIntervention();
            $signalements = $this->signalementRepository->findFromInactiveTerritories();
            $result[2] = \count($signalements);
            $signalements = $this->signalementRepository->findErpTransportsSignalements();
            $result[3] = \count($signalements);
        } else {
            $result[0] = $this->signalementRepository->countAvailableForEntrepriseWithoutAnswer($user->getEntreprise());
            $result[1] = $this->signalementRepository->countCurrentlyOpenForEntreprise($user->getEntreprise());
            $result[2] = 0;
            $result[3] = 0;
        }

        return $result;
    }

    public function createOrUpdate(array $data, bool $isImported = false): ?Signalement
    {
        /** @var Signalement|null $signalement */
        $signalement = $this->getRepository()->findOneBy([
            'reference' => $data['reference'],
        ]);

        if ($signalement instanceof Signalement) {
            return $this->update($signalement, $data);
        }

        $signalement = $this->signalementFactory->createInstanceFrom($data, $isImported);

        return $signalement;
    }

    public function update(Signalement $signalement, array $data): Signalement
    {
        return $signalement
            ->setReference($data['reference'])
            ->setEntreprise($data['entreprise'])
            ->setDeclarant($data['declarant'])
            ->setCreatedAtValue($data['createdAt'])
            ->setDateIntervention($data['dateIntervention'])
            ->setTypeLogement($data['typeLogement'])
            ->setLocalisationDansImmeuble($data['localisationDansImmeuble'])
            ->setAdresse($data['adresse'])
            ->setVille($data['ville'])
            ->setCodePostal($data['codePostal'])
            ->setNomOccupant($data['nomOccupant'])
            ->setPrenomOccupant($data['prenomOccupant'])
            ->setNiveauInfestation($data['niveauInfestation'])
            ->setNombrePiecesTraitees($data['nombrePiecesTraitees'])
            ->setDelaiEntreInterventions((int) $data['delaiEntreInterventions'])
            ->setFaitVisitePostTraitement($data['faitVisitePostTraitement'])
            ->setDateVisitePostTraitement($data['dateVisitePostTraitement'])
            ->setTypeIntervention($data['typeIntervention'])
            ->setTypeDiagnostic($data['typeDiagnostic'])
            ->setTypeTraitement($data['typeTraitement'])
            ->setNomBiocide(substr($data['nomBiocide'], 0, 50))
            ->setPrixFactureHT((int) $data['prixFactureHT'])
            ->setClosedAt($data['closedAt']);
    }
}
