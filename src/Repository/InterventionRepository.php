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
    private const NB_DAYS_BEFORE_NOTIFYING_USAGER = 30;
    private const NB_DAYS_BEFORE_NOTIFYING_PRO = 60;

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
                ->setParameter('entreprise', $entreprise)
            ->orderBy('i.id')
            ->setMaxResults(1);

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

    public function findInterventionsWithEstimation(Signalement $signalement)
    {
        $qb = $this->createQueryBuilder('i')
            ->where('i.signalement = :signalement')
                ->setParameter('signalement', $signalement)
            ->andWhere('i.estimationSentAt is not null');

        return $qb->getQuery()->getResult();
    }

    public function findToNotifyUsager(): array
    {
        return $this->createQueryBuilder('i')
            ->where('i.reminderResolvedByEntrepriseAt IS NULL')
            ->andWhere('i.resolvedByEntrepriseAt IS NOT NULL')
            ->andWhere('datediff(CURRENT_DATE(), i.resolvedByEntrepriseAt) > :nb_days_before_notifying')
                ->setParameter('nb_days_before_notifying', self::NB_DAYS_BEFORE_NOTIFYING_USAGER)
            ->getQuery()
            ->getResult();
    }

    public function findToNotifyPro(): array
    {
        return $this->createQueryBuilder('i')
            ->where('i.resolvedByEntrepriseAt IS NULL')
            ->andWhere('i.reminderPendingEntrepriseConclusionAt IS NULL')
            ->andWhere('i.canceledByEntrepriseAt IS NULL')
            ->andWhere('i.acceptedByUsager = 1')
            ->andWhere('datediff(CURRENT_DATE(), i.choiceByUsagerAt) > :nb_days_before_notifying')
                ->setParameter('nb_days_before_notifying', self::NB_DAYS_BEFORE_NOTIFYING_PRO)
            ->leftJoin('i.signalement', 's')
                ->andWhere('s.closedAt IS NULL')
                ->andWhere('s.resolvedAt IS NULL')
            ->getQuery()
            ->getResult();
    }
}
