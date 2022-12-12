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

    public function countOpenWithoutIntervention(): int
    {
        $qb = $this->createQueryBuilder('s')
            ->select('COUNT(s.id) as count')
            ->leftJoin('s.territoire', 't')
                ->where('t.active = true')
            ->andWhere('s.resolvedAt IS NULL')
            ->andWhere('s.closedAt IS NULL')
            ->andWhere('s.declarant = :declarant')
                ->setParameter('declarant', Declarant::DECLARANT_OCCUPANT);
        // TODO : where no intervention linked

        return $qb->getQuery()
            ->getSingleScalarResult();
    }

    public function countOpenWithIntervention(): int
    {
        $qb = $this->createQueryBuilder('s')
            ->select('COUNT(s.id) as count')
            ->leftJoin('s.territoire', 't')
                ->where('t.active = true')
            ->andWhere('s.resolvedAt IS NULL')
            ->andWhere('s.closedAt IS NULL')
            ->andWhere('s.declarant = :declarant')
                ->setParameter('declarant', Declarant::DECLARANT_OCCUPANT);
        // TODO : where intervention linked

        return $qb->getQuery()
            ->getSingleScalarResult();
    }

    public function countAvailableForEntrepriseWithoutAnswer(Entreprise $entreprise): int
    {
        $qb = $this->createQueryBuilder('s')
            ->select('COUNT(s.id) as count')
            ->leftJoin('s.territoire', 't')
                ->where('t.active = true')
            ->andWhere('s.resolvedAt IS NULL')
            ->andWhere('s.closedAt IS NULL')
            ->andWhere('s.declarant = :declarant')
                ->setParameter('declarant', Declarant::DECLARANT_OCCUPANT)
            ->andWhere('s.autotraitement != true')
            ->andWhere('s.territoire IN (:territoires)')
                ->setParameter('territoires', $entreprise->getTerritoires());
        // TODO : where no intervention linked to this entreprise

        return $qb->getQuery()
            ->getSingleScalarResult();
    }

    public function countCurrentlyOpenForEntreprise(Entreprise $entreprise): int
    {
        $qb = $this->createQueryBuilder('s')
            ->select('COUNT(s.id) as count')
            ->leftJoin('s.territoire', 't')
                ->where('t.active = true')
            ->andWhere('s.resolvedAt IS NULL')
            ->andWhere('s.closedAt IS NULL')
            ->andWhere('s.declarant = :declarant')
                ->setParameter('declarant', Declarant::DECLARANT_OCCUPANT)
            ->andWhere('s.autotraitement != true')
            ->andWhere('s.territoire IN (:territoires)')
                ->setParameter('territoires', $entreprise->getTerritoires());
        // TODO : where intervention linked to this entreprise and this signalement

        return $qb->getQuery()
            ->getSingleScalarResult();
    }
}
