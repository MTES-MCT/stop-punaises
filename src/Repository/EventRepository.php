<?php

namespace App\Repository;

use App\Entity\Event;
use App\Entity\Message;
use App\Entity\Signalement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Event>
 *
 * @method Event|null find($id, $lockMode = null, $lockVersion = null)
 * @method Event|null findOneBy(array $criteria, array $orderBy = null)
 * @method Event[]    findAll()
 * @method Event[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    public function findMessageEventsBySignalement(string $signalementUuid, string $recipient)
    {
        $qb = $this->createQueryBuilder('e');
        $qb->select('e.domain, e.title, e.description, e.label, e.actionLink, e.actionLabel, e.createdAt as date')
            ->where('e.entityName = :entityName')
            ->andWhere('e.entityUuid =:entityUuid')
            ->andWhere('e.domain = :domain_event')
            ->andWhere('e.recipient = :recipient')
            ->setParameter('entityName', Signalement::class)
            ->setParameter('entityUuid', $signalementUuid)
            ->setParameter('domain_event', Message::DOMAIN_NAME)
            ->setParameter('recipient', $recipient);

        return $qb->getQuery()->getResult();
    }

    public function save(Event $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Event $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
