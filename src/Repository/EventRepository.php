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

    public function findMessageEvents(string $signalementUuid, string $recipient = null, $lastMessageEvent = false): array
    {
        $qb = $this->createQueryBuilder('e');
        $qb->select('e.domain, e.title, e.description, e.actionLink, e.actionLabel')
            ->where('e.entityName = :entityName')
            ->setParameter('entityName', Signalement::class)
            ->andWhere('e.entityUuid =:entityUuid')
            ->setParameter('entityUuid', $signalementUuid)
            ->andWhere('e.domain = :domain_event')
            ->setParameter('domain_event', Message::DOMAIN_NAME);

        if (null !== $recipient) {
            $qb->addSelect('MAX(e.createdAt) as date')
                ->andWhere('e.recipient = :recipient')
                ->setParameter('recipient', $recipient)
                ->groupBy('e.domain, e.title, e.description, e.actionLink, e.actionLabel');
        } else {
            $qb->addSelect('MAX(e.createdAt) as date')
                ->andWhere('e.actionLink is not null and e.userId is null')
                ->groupBy('e.domain, e.title, e.description, e.actionLink, e.actionLabel');
        }

        return array_map(function ($item) {
            return [
                'domain' => $item['domain'],
                'title' => $item['title'],
                'description' => $item['description'],
                'actionLink' => $item['actionLink'],
                'actionLabel' => $item['actionLabel'],
                'date' => new \DateTimeImmutable($item['date']),
            ];
        }, $qb->getQuery()->getResult());
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
