<?php

namespace App\Repository;

use App\Entity\EntreprisePublique;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EntreprisePublique>
 *
 * @method EntreprisePublique|null find($id, $lockMode = null, $lockVersion = null)
 * @method EntreprisePublique|null findOneBy(array $criteria, array $orderBy = null)
 * @method EntreprisePublique[]    findAll()
 * @method EntreprisePublique[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EntreprisePubliqueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EntreprisePublique::class);
    }

    public function save(EntreprisePublique $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(EntreprisePublique $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByZipCode(string $zipCode): array
    {
        $qb = $this->createQueryBuilder('ep');
        $qb->where('ep.codePostal LIKE :zipCode')
            ->setParameter('zipCode', $zipCode.'%')
            ->orderBy('ep.nom', 'ASC');

        return $qb->getQuery()->getResult();
    }
}
