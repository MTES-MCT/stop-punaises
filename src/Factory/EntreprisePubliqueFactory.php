<?php

namespace App\Factory;

use App\Entity\EntreprisePublique;

class EntreprisePubliqueFactory
{
    public function createInstanceFrom(array $data): EntreprisePublique
    {
        return (new EntreprisePublique())
        ->setNom($data['nom'])
        ->setAdresse($data['adresse'] ?? '')// TODO à enlever une fois qu'on aura les adresses des entreprises de détection canine
        ->setUrl($data['url'])
        ->setTelephone($data['telephone'])
        ->setZip($data['zip'])
        ->setIsDetectionCanine($data['detection_canine'])
        ->setIsIntervention($data['intervention'])
        ->setIsProOnly($data['is_pro_only']);
    }
}
