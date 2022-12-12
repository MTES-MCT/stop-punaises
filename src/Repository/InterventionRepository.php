<?php

namespace App\Repository;

use App\Entity\Entreprise;
use App\Entity\Intervention;
use App\Entity\Signalement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Intervention>
 *
 * @method Intervention|null find($id, $lockMode = null, $lockVersion = null)
 * @method Intervention|null findOneBy(array $criteria, array $orderBy = null)
 * @method Intervention[]    findAll()
 * @method Intervention[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InterventionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Intervention::class);
    }

    public function save(Intervention $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Intervention $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findBySignalementAndEntreprise(Signalement $signalement, Entreprise $entreprise)
    {
        $qb = $this->createQueryBuilder('i')
            ->where('i.signalement = :signalement')
                ->setParameter('signalement', $signalement)
            ->andWhere('i.entreprise = :entreprise')
                ->setParameter('entreprise', $entreprise);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findInterventionsWithMissingAnswerFromUsager(Signalement $signalement)
    {
        $qb = $this->createQueryBuilder('i')
            ->where('i.signalement = :signalement')
                ->setParameter('signalement', $signalement)
            ->andWhere('i.estimationSentAt is not null')
            ->andWhere('i.choiceByUsagerAt is null');

        return $qb->getQuery()->getResult();
    }

    public function findToNotify(): array
    {
        $nbDaysBeforeNotifying = 30;

        return $this->createQueryBuilder('s')
            ->where('s.reminderResolvedByEntrepriseAt IS NULL')
            ->andWhere('s.resolvedByEntrepriseAt IS NOT NULL')
            ->andWhere('datediff(CURRENT_DATE(), s.resolvedByEntrepriseAt) > '.$nbDaysBeforeNotifying)
            ->getQuery()
            ->getResult();
    }
}
