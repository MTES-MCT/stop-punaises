<?php

namespace App\Factory;

use App\Entity\Signalement;

class SignalementFactory
{
    public function createInstanceFrom(array $data): Signalement
    {
        return (new Signalement())
        ->setReference($data['reference'])
        ->setEntreprise($data['entreprise'])
        ->setDeclarant($data['declarant'])
        ->setCreatedAtValue()
        ->setDateIntervention($data['dateIntervention'])
        ->setTypeLogement($data['typeLogement'])
        ->setLocalisationDansImmeuble($data['localisationDansImmeuble'])
        ->setAdresse($data['adresse'])
        ->setVille($data['ville'])
        ->setCodePostal($data['codePostal'])
        ->setNomOccupant($data['nomOccupant'])
        ->setPrenomOccupant($data['prenomOccupant'])
        ->setNiveauInfestation($data['niveauInfestation'])
        ->setNombrePiecesTraitees($data['nombrePiecesTraitees'])
        ->setDelaiEntreInterventions((int) $data['delaiEntreInterventions'])
        ->setFaitVisitePostTraitement($data['faitVisitePostTraitement'])
        ->setDateVisitePostTraitement($data['dateVisitePostTraitement'])
        ->setTypeIntervention($data['typeIntervention'])
        ->setTypeDiagnostic($data['typeDiagnostic'])
        ->setTypeTraitement($data['typeTraitement'])
        ->setNomBiocide(substr($data['nomBiocide'], 0, 50))
        ->setPrixFactureHT((int) $data['prixFactureHT'])
        ->setClosedAt($data['closedAt']);
    }
}
