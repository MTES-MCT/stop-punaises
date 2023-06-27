<?php

namespace App\Manager;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectRepository;

abstract class AbstractManager implements ManagerInterface
{
    public function __construct(protected ManagerRegistry $managerRegistry, protected string $entityName = '')
    {
    }

    public function save($entity, bool $flush = true): void
    {
        $this->managerRegistry->getManager()->persist($entity);
        if ($flush) {
            $this->flush();
        }
    }

    public function remove($entity, bool $flush = true): void
    {
        $this->managerRegistry->getManager()->remove($entity);
        if ($flush) {
            $this->flush();
        }
    }

    public function persist($entity): void
    {
        $this->save($entity, false);
    }

    public function flush(): void
    {
        $this->managerRegistry->getManager()->flush();
    }

    public function findOneBy(array $criteria)
    {
        return $this->getRepository()->findOneBy($criteria);
    }

    public function findBy(array $criteria)
    {
        return $this->getRepository()->findBy($criteria);
    }

    public function findAll()
    {
        return $this->getRepository()->findAll();
    }

    public function getRepository(): ObjectRepository
    {
        return $this->managerRegistry->getRepository($this->entityName);
    }
}
