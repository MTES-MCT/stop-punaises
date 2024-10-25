<?php

namespace App\Repository;

use App\Dto\SignalementOccupantDataTableFilters;
use App\Entity\Entreprise;
use App\Entity\Enum\Declarant;
use App\Entity\Enum\ProcedureProgress;
use App\Entity\Enum\SignalementStatus;
use App\Entity\Enum\SignalementType;
use App\Entity\Signalement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Signalement>
 *
 * @method Signalement|null find($id, $lockMode = null, $lockVersion = null)
 * @method Signalement|null findOneBy(array $criteria, array $orderBy = null)
 * @method Signalement[]    findAll()
 * @method Signalement[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SignalementRepository extends ServiceEntityRepository
{
    private const NB_DAYS_BEFORE_NOTIFYING = 45;
    private const NB_DAYS_BEFORE_CLOSING_AUTOTRAITEMENT = 45;
    public const MARKERS_PAGE_SIZE = 6000; // @todo: is high cause duplicate result, the query findAllWithGeoData should be reviewed

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Signalement::class);
    }

    public function save(Signalement $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Signalement $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findOneByUuid(string $uuid)
    {
        $qb = $this->createQueryBuilder('s')
            ->where('s.uuid = :uuid')
            ->setParameter('uuid', $uuid);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findLastReference(?string $year = null): ?array
    {
        if (null === $year) {
            $year = (new \DateTime())->format('Y');
        }
        $queryBuilder = $this->createQueryBuilder('s')
            ->select('s.reference')
            ->where('YEAR(s.createdAt) = :year')
            ->setParameter('year', $year)
            ->orderBy('s.id', 'DESC')
            ->setMaxResults(1);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    public function findFromInactiveTerritories(): ?array
    {
        return $this->createQueryBuilder('s')
            ->leftJoin('s.territoire', 't')
            ->where('t.active != true')
            ->andWhere('s.type = :typeLogement')
            ->setParameter('typeLogement', SignalementType::TYPE_LOGEMENT->value)
            ->getQuery()
            ->getResult();
    }

    public function findErpTransportsSignalements(): ?array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.type != :typeLogement')
            ->setParameter('typeLogement', SignalementType::TYPE_LOGEMENT->value)
            ->getQuery()
            ->getResult();
    }

    private function buildSelectProcedure(): string
    {
        return 'CASE
            WHEN (s.autotraitement = true) THEN
                CASE
                WHEN (s.resolved_at IS NOT NULL OR s.closed_at IS NOT NULL) THEN
                    \''.ProcedureProgress::AUTO_CONFIRMATION_USAGER->value.'\'
                WHEN (s.reminder_autotraitement_at IS NOT NULL) THEN
                    \''.ProcedureProgress::AUTO_FEEDBACK_ENVOYE->value.'\'
                ELSE
                    \''.ProcedureProgress::AUTO_PROTOCOLE_ENVOYE->value.'\'
                END
            ELSE
                CASE
                WHEN (i.id IS NOT NULL) THEN
                    CASE
                    WHEN (s.resolved_at IS NOT NULL) THEN
                        \''.ProcedureProgress::AUTO_CONFIRMATION_USAGER->value.'\'
                    WHEN (s.type_intervention IS NOT NULL) THEN
                        \''.ProcedureProgress::PRO_INTERVENTION_FAITE->value.'\'
                    WHEN (SELECT COUNT(*) > 0 FROM intervention ip3 WHERE s.id = ip3.signalement_id AND ip3.accepted_by_usager = true) THEN
                        \''.ProcedureProgress::PRO_ESTIMATION_ACCEPTEE->value.'\'
                    WHEN (SELECT COUNT(*) > 0 FROM intervention ip2 WHERE s.id = ip2.signalement_id AND ip2.canceled_by_entreprise_at IS NOT NULL) THEN
                        \''.ProcedureProgress::PRO_INTERVENTION_ANNULEE->value.'\'
                    WHEN (SELECT COUNT(*) > 0 FROM intervention ip4 WHERE s.id = ip4.signalement_id AND ip4.accepted_by_usager = false) THEN
                        \''.ProcedureProgress::PRO_ESTIMATION_REFUSEE->value.'\'
                    ELSE
                        \''.ProcedureProgress::PRO_ESTIMATION_ENVOYEE->value.'\'
                    END
                ELSE
                    \''.ProcedureProgress::PRO_RECEPTION->value.'\'
                END
            END AS procedure_progress';
    }

    private function buildSelectStatut(?Entreprise $entreprise): string
    {
        if (empty($entreprise)) {
            return 'CASE
                WHEN (s.resolved_at IS NOT NULL OR s.closed_at IS NOT NULL) THEN
                    \''.SignalementStatus::CLOSED->value.'\'
                WHEN (s.autotraitement != 1 AND i.id IS NOT NULL) THEN
                    CASE
                    WHEN (s.type_intervention IS NOT NULL AND s.type_intervention != \'\') THEN
                        \''.SignalementStatus::PROCESSED->value.'\'
                    ELSE
                        \''.SignalementStatus::ACTIVE->value.'\'
                    END
                ELSE
                    \''.SignalementStatus::NEW->value.'\'
                END AS statut';
        }

        return 'CASE
                WHEN (s.resolved_at IS NOT NULL OR s.closed_at IS NOT NULL OR s.autotraitement = 1) THEN
                    \''.SignalementStatus::CLOSED->value.'\'
                WHEN (
                    i.id IS NOT NULL AND (
                        SELECT COUNT(*) > 0 FROM intervention is2 WHERE s.id = is2.signalement_id AND is2.entreprise_id = '.$entreprise->getId().'
                    )
                ) THEN
                    CASE
                    WHEN (i.accepted != true AND i.canceled_by_entreprise_at IS NOT NULL AND i.entreprise_id = '.$entreprise->getId().') THEN
                        \''.SignalementStatus::CANCELED->value.'\'
                    WHEN (i.accepted != true AND i.entreprise_id = '.$entreprise->getId().') THEN
                        \''.SignalementStatus::REFUSED->value.'\'
                    WHEN (i.accepted_by_usager = false AND i.entreprise_id = '.$entreprise->getId().') THEN
                        \''.SignalementStatus::REFUSED->value.'\'
                    WHEN (i.accepted = true AND i.accepted_by_usager = true AND s.type_intervention IS NOT NULL AND s.type_intervention != \'\' AND i.entreprise_id = '.$entreprise->getId().') THEN
                        \''.SignalementStatus::PROCESSED->value.'\'
                    WHEN (i.accepted = true AND i.entreprise_id = '.$entreprise->getId().') THEN
                        \''.SignalementStatus::ACTIVE->value.'\'
                    END
                WHEN i.id IS NOT NULL THEN
                    CASE
                    WHEN (i.accepted = true AND i.accepted_by_usager = true AND i.entreprise_id != '.$entreprise->getId().') THEN
                        \''.SignalementStatus::CLOSED->value.'\'
                    ELSE
                        \''.SignalementStatus::ACTIVE->value.'\'
                    END
                ELSE
                    \''.SignalementStatus::NEW->value.'\'
                END AS statut';
    }

    public function findDeclaredByOccupants(
        ?Entreprise $entreprise = null,
        ?string $start = null,
        ?string $length = null,
        ?string $orderColumn = null,
        ?string $orderDirection = null,
        ?SignalementOccupantDataTableFilters $filters = null,
    ): array|int {
        $connexion = $this->getEntityManager()->getConnection();

        $parameters = [];

        $sql = 'SELECT DISTINCT s.id, s.*';
        if (empty($entreprise)) {
            $sql .= ', '.$this->buildSelectProcedure();
        }
        $sql .= ', '.$this->buildSelectStatut($entreprise);
        $sql .= ' FROM signalement s';
        $sql .= ' LEFT JOIN intervention i ON i.signalement_id = s.id';
        $sql .= ' LEFT JOIN territoire t ON s.territoire_id = t.id';

        $sql .= ' WHERE t.active = 1';
        $sql .= ' AND s.declarant LIKE :declarant';
        $parameters['declarant'] = Declarant::DECLARANT_OCCUPANT->value;

        if (!empty($entreprise)) {
            $sql .= ' AND s.autotraitement != true';
            $territoiresId = '';
            foreach ($entreprise->getTerritoires() as $territoire) {
                if (!empty($territoiresId)) {
                    $territoiresId .= ',';
                }
                $territoiresId .= $territoire->getId();
            }
            if (!empty($territoiresId)) {
                $sql .= ' AND s.territoire_id IN ('.$territoiresId.')';
            }
        }

        if (!empty($filters)) {
            if (!empty($filters->getZip())) {
                $sql .= ' AND t.zip = :zip';
                $parameters['zip'] = $filters->getZip();
            }
            if (!empty($filters->getDate())) {
                $sql .= ' AND DATE(s.created_at) = :date';
                $parameters['date'] = $filters->getDate();
            }
            if (!empty($filters->getNiveauInfestation()) || '0' === $filters->getNiveauInfestation()) {
                $sql .= ' AND s.niveau_infestation = :niveauInfestation';
                $parameters['niveauInfestation'] = $filters->getNiveauInfestation();
            }
            if (!empty($filters->getAdresse())) {
                $sql .= ' AND (s.code_postal LIKE :adresse OR s.ville LIKE :adresse)';
                $parameters['adresse'] = '%'.$filters->getAdresse().'%';
            }
            if (!empty($filters->getType())) {
                if ('a-traiter' === $filters->getType()) {
                    $sql .= ' AND (s.logement_social != true OR s.logement_social IS NULL)';
                    $sql .= ' AND (s.autotraitement != true OR s.autotraitement IS NULL)';
                } elseif ('auto-traitement' === $filters->getType()) {
                    $sql .= ' AND s.autotraitement = true';
                }
            }
            if (!empty($filters->getEtatInfestation())) {
                if ('infestation-resolu' === $filters->getEtatInfestation()) {
                    $sql .= ' AND s.resolved_at IS NOT NULL';
                } elseif ('infestation-nonresolu' === $filters->getEtatInfestation()) {
                    $sql .= ' AND s.resolved_at IS NULL';
                }
            }
            if (!empty($filters->getMotifCloture())) {
                if ('motif-resolu' === $filters->getMotifCloture()) {
                    $sql .= ' AND s.resolved_at IS NOT NULL';
                } elseif ('motif-refuse' === $filters->getMotifCloture()) {
                    $sql .= ' AND i.id IS NOT NULL';

                    $subquery = 'SELECT DISTINCT interv.signalement_id';
                    $subquery .= ' FROM intervention interv';
                    $subquery .= ' WHERE interv.accepted_by_usager IS NULL';
                    $subquery .= ' OR interv.accepted_by_usager = true';
                    $sql .= ' AND s.id NOT IN ('.$subquery.')';
                } elseif ('motif-arret' === $filters->getMotifCloture()) {
                    $sql .= ' AND s.closed_at IS NOT NULL';
                }
            }
        }

        $sql .= ' HAVING statut IS NOT NULL';
        if (!empty($filters)) {
            if (!empty($filters->getStatut())) {
                $sql .= ' AND statut = :statut';
                $parameters['statut'] = SignalementStatus::from($filters->getStatut())->value;
            }
        }

        if (!empty($orderColumn)) {
            $orderDirection = strtoupper($orderDirection);
            if (!in_array($orderDirection, ['ASC', 'DESC'])) {
                $orderDirection = 'DESC';
            }
            switch ($orderColumn) {
                case 'id':
                    $sql .= ' ORDER BY s.id '.$orderDirection;
                    break;
                case 'date':
                    $sql .= ' ORDER BY s.created_at '.$orderDirection;
                    break;
                case 'infestation':
                    $sql .= ' ORDER BY s.niveau_infestation '.$orderDirection;
                    break;
                case 'commune':
                    $sql .= ' ORDER BY s.code_postal '.$orderDirection;
                    $sql .= ', s.ville '.$orderDirection;
                    break;
                case 'type':
                    $sql .= ' ORDER BY s.logement_social '.$orderDirection;
                    $sql .= ', s.autotraitement '.$orderDirection;
                    break;
                case 'procedure':
                    $sql .= ' ORDER BY procedure_progress '.$orderDirection;
                    break;
                case 'statut':
                    $sql .= ' ORDER BY statut '.$orderDirection;
                    break;
                default:
                    break;
            }
        }

        if (!empty($length)) {
            $sql .= ' LIMIT '.(int) $length;
        }
        if (!empty($start)) {
            $sql .= ' OFFSET '.(int) $start;
        }

        $statement = $connexion->prepare($sql);

        return $statement->executeQuery($parameters)->fetchAllAssociative();
    }

    public function findToNotify(): ?array
    {
        return $this->createQueryBuilder('s')
            ->where('s.reminderAutotraitementAt IS NULL')
            ->andWhere('s.autotraitement = true')
            ->andWhere('s.declarant = :declarant')
                ->setParameter('declarant', Declarant::DECLARANT_OCCUPANT)
            ->andWhere('(s.switchedTraitementAt IS NULL AND datediff(CURRENT_DATE(), s.createdAt) > :nb_days_before_notifying) OR (s.switchedTraitementAt IS NOT NULL AND DATEDIFF(CURRENT_DATE(), s.switchedTraitementAt) > :nb_days_before_notifying)')
                ->setParameter('nb_days_before_notifying', self::NB_DAYS_BEFORE_NOTIFYING)
            ->getQuery()
            ->getResult();
    }

    public function findTraitementAutoToClose(): ?array
    {
        return $this->createQueryBuilder('s')
            ->where('s.reminderAutotraitementAt IS NOT NULL')
            ->andWhere('s.autotraitement = true')
            ->andWhere('s.resolvedAt IS NULL')
            ->andWhere('s.closedAt IS NULL')
            ->andWhere('s.declarant = :declarant')
                ->setParameter('declarant', Declarant::DECLARANT_OCCUPANT)
            ->andWhere('datediff(CURRENT_DATE(), s.reminderAutotraitementAt) > :nb_days_before_notifying')
                ->setParameter('nb_days_before_notifying', self::NB_DAYS_BEFORE_CLOSING_AUTOTRAITEMENT)
            ->getQuery()
            ->getResult();
    }

    public function findTraitementProWithNoEstimationAccepted(): ?array
    {
        $connection = $this->getEntityManager()->getConnection();

        $sql = 'SELECT s.id
                FROM signalement s
                WHERE s.autotraitement = 0
                AND s.resolved_at IS NULL
                AND s.closed_at IS NULL
                AND NOT EXISTS (
                    SELECT *
                    FROM intervention i
                    WHERE i.signalement_id = s.id
                    AND i.accepted_by_usager = true
                )
                AND s.declarant = \''.Declarant::DECLARANT_OCCUPANT->value.'\'';

        $statement = $connection->prepare($sql);

        return $statement->executeQuery()->fetchAllAssociative();
    }

    public function countOpenWithoutIntervention(): int
    {
        $qb = $this->createQueryBuilder('s')
            ->select('COUNT(DISTINCT s.id) as count')
            ->leftJoin('s.territoire', 't')
                ->where('t.active = true')
            ->leftJoin('s.interventions', 'i')
                ->andWhere('i.id IS NULL OR s.autotraitement = true')
            ->andWhere('s.resolvedAt IS NULL')
            ->andWhere('s.closedAt IS NULL')
            ->andWhere('s.declarant = :declarant')
                ->setParameter('declarant', Declarant::DECLARANT_OCCUPANT);

        return $qb->getQuery()
            ->getSingleScalarResult();
    }

    public function countOpenWithIntervention(): int
    {
        $qb = $this->createQueryBuilder('s')
            ->select('COUNT(DISTINCT s.id) as count')
            ->leftJoin('s.territoire', 't')
                ->where('t.active = true')
            ->leftJoin('s.interventions', 'i')
                ->andWhere('i.id IS NOT NULL AND s.autotraitement = false')
            ->andWhere('s.resolvedAt IS NULL')
            ->andWhere('s.closedAt IS NULL')
            ->andWhere('s.declarant = :declarant')
                ->setParameter('declarant', Declarant::DECLARANT_OCCUPANT);

        return $qb->getQuery()
            ->getSingleScalarResult();
    }

    public function countAvailableForEntrepriseWithoutAnswer(Entreprise $entreprise): int
    {
        $connection = $this->getEntityManager()->getConnection();
        $sql = '
        SELECT COUNT(DISTINCT s.id)
        FROM `signalement` s
        WHERE
            s.resolved_at IS NULL
            AND s.closed_at IS NULL
            AND s.declarant LIKE \'DECLARANT_OCCUPANT\'
            AND s.autotraitement = FALSE
            AND s.territoire_id IN (:territoires)
            AND s.id not in (
                SELECT i.signalement_id
                FROM intervention i
                WHERE i.entreprise_id = :entrepriseId
                    OR i.accepted_by_usager = true
            )
        ';

        return $connection->executeQuery(
            $sql,
            [
                'territoires' => $entreprise->getTerritoireIds(),
                'entrepriseId' => $entreprise->getId(),
            ],
            [
                'territoires' => Connection::PARAM_INT_ARRAY,
            ]
        )->fetchOne();
    }

    public function countCurrentlyOpenForEntreprise(Entreprise $entreprise): int
    {
        $connection = $this->getEntityManager()->getConnection();
        $sql = '
        SELECT COUNT(DISTINCT s.id)
        FROM `signalement` s
        WHERE
            s.resolved_at IS NULL
            AND s.closed_at IS NULL
            AND s.declarant LIKE \'DECLARANT_OCCUPANT\'
            AND s.autotraitement = FALSE
            AND s.territoire_id IN (:territoires)
            AND s.id in (
                SELECT i.signalement_id
                FROM intervention i
                WHERE i.entreprise_id = :entrepriseId
                AND i.accepted = 1
                AND i.resolved_by_entreprise_at IS NULL
                AND (i.accepted_by_usager = true OR i.accepted_by_usager IS NULL)
            )
        ';

        return $connection->executeQuery(
            $sql,
            [
                'territoires' => $entreprise->getTerritoireIds(),
                'entrepriseId' => $entreprise->getId(),
            ],
            [
                'territoires' => Connection::PARAM_INT_ARRAY,
            ]
        )->fetchOne();
    }

    public function findAllWithGeoData(
        \DateTimeImmutable $date,
        float $swLat,
        float $swLng,
        float $neLat,
        float $neLng,
    ): array {
        $conn = $this->getEntityManager()->getConnection();
        $limit = self::MARKERS_PAGE_SIZE;
        $sql = "
            SELECT DISTINCT s.created_at, s.niveau_infestation, s.resolved_at, s.closed_at, s.geoloc
            FROM signalement s
            WHERE s.created_at < :date
            AND s.geoloc != 'null'
            AND s.geoloc != '[]'
            AND CAST(JSON_UNQUOTE(JSON_EXTRACT(s.geoloc, '$.lat')) AS DECIMAL(10, 6)) BETWEEN :swLat AND :neLat
            AND CAST(JSON_UNQUOTE(JSON_EXTRACT(s.geoloc, '$.lng')) AS DECIMAL(10, 6)) BETWEEN :swLng AND :neLng
            ORDER BY s.created_at DESC
            LIMIT $limit;
        ";

        $resultSet = $conn->executeQuery($sql, [
            'date' => $date->format('Y-m-d H:i:s'),
            'swLat' => $swLat,
            'neLat' => $neLat,
            'swLng' => $swLng,
            'neLng' => $neLng,
        ]);

        return $resultSet->fetchAllAssociative();
    }
}
