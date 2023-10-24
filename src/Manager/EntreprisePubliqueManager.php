<?php

namespace App\Manager;

use App\Entity\EntreprisePublique;
use App\Factory\EntreprisePubliqueFactory;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\SecurityBundle\Security;

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
        if (null !== $data['nom']) {
            $entreprisePublique->setNom($data['nom']);
        }
        if (null !== $data['adresse']) {
            $entreprisePublique->setAdresse($data['adresse']);
        }
        if (null !== $data['url']) {
            $entreprisePublique->setUrl($data['url']);
        }
        if (null !== $data['telephone']) {
            $entreprisePublique->setTelephone($data['telephone']);
        }
        if (null !== $data['zip']) {
            $entreprisePublique->setZip($data['zip']);
        }
        if (null !== $data['detection_canine']) {
            $entreprisePublique->setIsDetectionCanine($data['detection_canine']);
        }
        if (null !== $data['intervention']) {
            $entreprisePublique->setIsIntervention($data['intervention']);
        }
        if (null !== $data['is_pro_only']) {
            $entreprisePublique->setIsProOnly($data['is_pro_only']);
        }

        return $entreprisePublique;
    }
}
