<?php

namespace App\Repository;

use App\Entity\Entreprise;
use App\Entity\Enum\Declarant;
use App\Entity\Enum\SignalementType;
use App\Entity\Intervention;
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
    public const MARKERS_PAGE_SIZE = 9000; // @todo: is high cause duplicate result, the query findAllWithGeoData should be reviewed

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

    public function findDeclaredByOccupants(
        Entreprise|null $entreprise = null,
        ?string $start,
        ?string $length,
        ?string $zip,
        ?string $statut,
        ?string $date,
        ?string $niveauInfestation,
        ?string $adresse,
        ?string $type,
        ?string $etatInfestation,
        ?string $motifCloture,
    ): ?array {
        $qb = $this->createQueryBuilder('s')
            ->leftJoin('s.territoire', 't')
            ->where('t.active = true')
            ->andWhere('s.declarant = :declarant')
                ->setParameter('declarant', Declarant::DECLARANT_OCCUPANT);

        if (!empty($entreprise)) {
            $qb->andWhere('s.autotraitement != true')
                ->andWhere('s.territoire IN (:territoires)')
                    ->setParameter('territoires', $entreprise->getTerritoires());
        }
        if (!empty($zip)) {
            $qb->andWhere('t.zip = :zip')
                ->setParameter('zip', $zip);
        }
        if (!empty($date)) {
            $qb->andWhere('DATE(s.createdAt) = :date')
                ->setParameter('date', $date);
        }
        if (!empty($niveauInfestation) || '0' === $niveauInfestation) {
            $qb->andWhere('s.niveauInfestation = :infestation')
                ->setParameter('infestation', $niveauInfestation);
        }
        if (!empty($adresse)) {
            $qb->andWhere('s.codePostal LIKE :adresse OR s.ville LIKE :adresse')
                ->setParameter('adresse', '%'.$adresse.'%');
        }
        if (!empty($type)) {
            if ('a-traiter' === $type) {
                $qb->andWhere('s.logementSocial != true OR s.logementSocial IS NULL')
                    ->andWhere('s.autotraitement != true OR s.autotraitement IS NULL');
            } elseif ('auto-traitement' === $type) {
                $qb->andWhere('s.autotraitement = true');
            }
        }
        if (!empty($etatInfestation)) {
            if ('infestation-resolu' === $etatInfestation) {
                $qb->andWhere('s.resolvedAt IS NOT NULL');
            } elseif ('infestation-nonresolu' === $etatInfestation) {
                $qb->andWhere('s.resolvedAt IS NULL');
            }
        }
        if (!empty($motifCloture)) {
            switch ($motifCloture) {
                case 'motif-resolu':
                    $qb->andWhere('s.resolvedAt IS NOT NULL');
                    break;
                case 'motif-refuse':
                    $qb->leftJoin('s.interventions', 'i')
                        ->andWhere('i.id IS NOT NULL');

                    $subquery = $this->_em->createQueryBuilder()
                        ->select('IDENTITY(interv.signalement)')
                        ->from(Intervention::class, 'interv')
                        ->where('interv.acceptedByUsager IS NULL')
                        ->orWhere('interv.acceptedByUsager = true')
                        ->distinct();
                    $subqueryResult = $subquery->getQuery()->getSingleColumnResult();

                    if (!empty($subqueryResult)) {
                        $qb->andWhere('s.id NOT IN (:subquery)')
                            ->setParameter('subquery', $subqueryResult);
                    }
                    break;
                case 'motif-arret':
                    $qb->andWhere('s.closedAt IS NOT NULL');
                    break;

                default:
                    break;
            }
        }

        if (!empty($start)) {
            $qb->setFirstResult($start);
        }
        if (!empty($length)) {
            $qb->setMaxResults($length);
        }

        return $qb->getQuery()
            ->getResult();
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

    public function findAllWithGeoData(\DateTimeImmutable $date, int $offset): array
    {
        $firstResult = $offset;
        $qb = $this->createQueryBuilder('s');
        $qb->select('
            DISTINCT s.id,
            s.uuid,
            s.reference,
            s.createdAt,
            s.nomOccupant,
            s.prenomOccupant,
            s.adresse,
            s.codePostal,
            s.ville,
            s.niveauInfestation,
            s.resolvedAt,
            s.closedAt,
            s.geoloc,
            t.active')
            ->leftJoin('s.territoire', 't')
            ->where('s.createdAt < :date')
            ->setParameter('date', $date);

        $qb->setFirstResult($firstResult)
            ->setMaxResults(self::MARKERS_PAGE_SIZE);

        return $qb->getQuery()->getArrayResult();
    }
}
