<?php

namespace App\Manager;

use App\Entity\EntreprisePublique;
use App\Factory\EntreprisePubliqueFactory;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Security;

class EntreprisePubliqueManager extends AbstractManager
{
    public function __construct(
        protected ManagerRegistry $managerRegistry,
        private Security $security,
        private EntreprisePubliqueFactory $entreprisePubliqueFactory,
        protected string $entityName = EntreprisePublique::class,
    ) {
        parent::__construct($managerRegistry, $entityName);
    }

    public function createOrUpdate(array $data): ?EntreprisePublique
    {
        /** @var EntreprisePublique|null $entreprisePublique */
        $entreprisePublique = $this->getRepository()->findOneBy([
            'nom' => $data['nom'],
            'zip' => $data['zip'],
        ]);

        if ($entreprisePublique instanceof EntreprisePublique) {
            return $this->update($entreprisePublique, $data);
        }

        return $this->entreprisePubliqueFactory->createInstanceFrom($data);
    }

    public function update(EntreprisePublique $entreprisePublique, array $data): EntreprisePublique
    {
        return $entreprisePublique
            ->setNom($data['nom'])
            ->setAdresse($data['adresse'])
            ->setUrl($data['url'])
            ->setTelephone($data['telephone'])
            ->setZip($data['zip']);
    }
}
