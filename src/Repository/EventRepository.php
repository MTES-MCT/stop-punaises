<?php

namespace App\Repository;

use App\Entity\Event;
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

    public function findActiveDomainEvents(string $signalementUuid, string $domain, ?int $userId, ?string $recipient, ?string $title): array
    {
        $qb = $this->createQueryBuilder('e');
        $qb->where('e.entityName = :entityName')
            ->setParameter('entityName', Signalement::class)
            ->andWhere('e.entityUuid = :entityUuid')
            ->setParameter('entityUuid', $signalementUuid)
            ->andWhere('e.domain = :domainEvent')
            ->setParameter('domainEvent', $domain);

        if (null !== $userId) {
            $qb->andWhere('e.userId = :userId')
                ->setParameter('userId', $userId);
        } else {
            $qb->andWhere('e.userId IS NULL');
        }

        if (null !== $recipient) {
            $qb->andWhere('e.recipient = :recipient')
                ->setParameter('recipient', $recipient);
        } else {
            $qb->andWhere('e.recipient IS NULL');
        }

        if (!empty($title)) {
            $qb->andWhere('e.title = :title')
                ->setParameter('title', $title);
        }

        return $qb->getQuery()->getResult();
    }

    public function findAdminEvents(string $signalementUuid): array
    {
        $qb = $this->createQueryBuilder('e');
        $qb->select('e.domain, e.title, e.description, e.label, e.actionLink, e.actionLabel, e.createdAt')
            ->where('e.entityName = :entityName')
            ->setParameter('entityName', Signalement::class)
            ->andWhere('e.entityUuid = :entityUuid')
            ->setParameter('entityUuid', $signalementUuid)
            ->andWhere('e.userId = :userIdAdmin OR e.userId = :userIdAll')
            ->setParameter('userIdAll', Event::USER_ALL)
            ->setParameter('userIdAdmin', Event::USER_ADMIN)
            ->andWhere('e.active = 1')
            ->orderBy('e.createdAt', 'DESC');

        return array_map(function ($item) {
            return [
                'domain' => $item['domain'],
                'title' => $item['title'],
                'description' => $item['description'],
                'label' => $item['label'],
                'actionLink' => $item['actionLink'],
                'actionLabel' => $item['actionLabel'],
                'date' => $item['createdAt'],
            ];
        }, $qb->getQuery()->getResult());
    }

    public function findEntrepriseEvents(string $signalementUuid, int $userId): array
    {
        $qb = $this->createQueryBuilder('e');
        $qb->select('e.domain, e.title, e.description, e.label, e.actionLink, e.actionLabel, e.createdAt')
            ->where('e.entityName = :entityName')
            ->setParameter('entityName', Signalement::class)
            ->andWhere('e.entityUuid = :entityUuid')
            ->setParameter('entityUuid', $signalementUuid)
            ->andWhere('e.userId = :userId OR e.userId = :userIdAll')
            ->setParameter('userId', $userId)
            ->setParameter('userIdAll', Event::USER_ALL)
            ->andWhere('e.userIdExcluded IS NULL OR e.userIdExcluded != :userIdExcluded')
            ->setParameter('userIdExcluded', $userId)
            ->andWhere('e.active = 1')
            ->orderBy('e.createdAt', 'DESC');

        return array_map(function ($item) {
            return [
                'domain' => $item['domain'],
                'title' => $item['title'],
                'description' => $item['description'],
                'label' => $item['label'],
                'actionLink' => $item['actionLink'],
                'actionLabel' => $item['actionLabel'],
                'date' => $item['createdAt'],
            ];
        }, $qb->getQuery()->getResult());
    }

    public function findUsagerEvents(string $signalementUuid): array
    {
        $qb = $this->createQueryBuilder('e');
        $qb->select('e.domain, e.title, e.description, e.label, e.actionLink, e.actionLabel, e.createdAt')
            ->where('e.entityName = :entityName')
            ->setParameter('entityName', Signalement::class)
            ->andWhere('e.entityUuid = :entityUuid')
            ->setParameter('entityUuid', $signalementUuid)
            ->andWhere('e.userId IS NULL')
            ->andWhere('e.active = 1')
            ->orderBy('e.createdAt', 'DESC');

        return array_map(function ($item) {
            return [
                'domain' => $item['domain'],
                'title' => $item['title'],
                'description' => $item['description'],
                'label' => $item['label'],
                'actionLink' => $item['actionLink'],
                'actionLabel' => $item['actionLabel'],
                'date' => $item['createdAt'],
            ];
        }, $qb->getQuery()->getResult());
    }

    public function updateCreatedAt(Event $event, ?\DateTimeImmutable $createdAt)
    {
        if ($createdAt) {
            $qb = $this->createQueryBuilder('e');
            $qb->update()
                ->set('e.createdAt', '\''.$createdAt->format('Y-m-d H:i:s').'\'')
                ->where('e.id = :eventId')
                ->setParameter('eventId', $event->getId());
            $qb->getQuery()->execute();
        }
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
