<?php

namespace App\Repository;

use App\Entity\Entreprise;
use App\Entity\Enum\Declarant;
use App\Entity\Signalement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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

    public function findLastReference(): ?array
    {
        $year = (new \DateTime())->format('Y');
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
            ->where('t.active != true')
            ->leftJoin('s.territoire', 't')
            ->getQuery()
            ->getResult();
    }

    public function findDeclaredByOccupants(Entreprise|null $entreprise = null): ?array
    {
        $qb = $this->createQueryBuilder('s')
            ->leftJoin('s.territoire', 't')
            ->where('t.active = true')
            ->andWhere('s.declarant = :declarant')
                ->setParameter('declarant', Declarant::DECLARANT_OCCUPANT);

        if (!empty($entreprise)) {
            $qb->andWhere('s.autotraitement != true')
                ->andWhere('s.entreprise IS NULL or s.entreprise = :entrepriseId')
                    ->setParameter('entrepriseId', $entreprise->getId())
                ->andWhere('s.territoire IN (:territoires)')
                    ->setParameter('territoires', $entreprise->getTerritoires());
        }

        return $qb->getQuery()
            ->getResult();
    }

    public function findToNotify(): ?array
    {
        $nbDaysBeforeNotifying = 45;

        return $this->createQueryBuilder('s')
            ->where('s.reminderAutotraitementAt IS NULL')
            ->andWhere('s.autotraitement = true')
            ->andWhere('s.declarant = :declarant')
                ->setParameter('declarant', Declarant::DECLARANT_OCCUPANT)
            ->andWhere('(s.switchedTraitementAt IS NULL AND datediff(CURRENT_DATE(), s.createdAt) > '.$nbDaysBeforeNotifying.') OR (s.switchedTraitementAt IS NOT NULL AND DATEDIFF(CURRENT_DATE(), s.switchedTraitementAt) > '.$nbDaysBeforeNotifying.')')
            ->getQuery()
            ->getResult();
    }

    public function countOpenWithoutIntervention(): int
    {
        $qb = $this->createQueryBuilder('s')
            ->select('COUNT(s.id) as count')
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
            ->select('COUNT(s.id) as count')
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
        SELECT COUNT(s.id)
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
            )
        ';

        $statement = $connection->prepare($sql);

        return $statement->executeQuery([
            'territoires' => $entreprise->getTerritoiresIdToString(),
            'entrepriseId' => $entreprise->getId(),
        ])->fetchOne();
    }

    public function countCurrentlyOpenForEntreprise(Entreprise $entreprise): int
    {
        $connection = $this->getEntityManager()->getConnection();
        $sql = '
        SELECT COUNT(s.id)
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
                AND (i.accepted_by_usager = true OR i.accepted_by_usager IS NULL)
            )
        ';

        $statement = $connection->prepare($sql);

        return $statement->executeQuery([
            'territoires' => $entreprise->getTerritoiresIdToString(),
            'entrepriseId' => $entreprise->getId(),
        ])->fetchOne();
    }
}
