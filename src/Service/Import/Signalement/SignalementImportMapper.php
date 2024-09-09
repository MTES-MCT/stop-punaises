<?php

namespace App\Service\Import\Signalement;

use App\Entity\Enum\InfestationLevel;

class SignalementImportMapper
{
    public function getMapping(): array
    {
        return [
            'Ref signalement' => 'reference',
            'Date de creation signalement' => 'createdAt',
            'Date cloture' => 'closedAt',
            'codeInsee' => 'codeInsee',
            'Date intervention' => 'dateIntervention',
            'Type Logement' => 'typeLogement',
            'Si immeuble' => 'localisationDansImmeuble',
            'Adresse' => 'adresse',
            'Ville' => 'ville',
            'Code postal' => 'codePostal',
            'Nom Occupant' => 'nomOccupant',
            'Prenom Occupant' => 'prenomOccupant',
            'Niveau infestation' => 'niveauInfestation',
            'Nombre de pièces traitées' => 'nombrePiecesTraitees',
            'Délai entre les interventions (en jours)' => 'delaiEntreInterventions',
            'Visite Post Traitement' => 'faitVisitePostTraitement',
            'Date Visite post traitement' => 'dateVisitePostTraitement',
            'Type d\'intervention' => 'typeIntervention',
            'Type de diagnostic (si pertinent)' => 'typeDiagnostic',
            'Type de traitement' => 'typeTraitement',
            'Nom biocide (si pertinent)' => 'nomBiocide',
            'Prix facturé' => 'prixFactureHT',
            'Entreprise' => 'entrepriseUUID',
        ];
    }

    public function map(array $columns, array $data): array
    {
        $dataMapped = [];
        if (1 === \count($data)) {
            return $dataMapped;
        }
        foreach ($this->getMapping() as $fileColumn => $fieldColumn) {
            if (\in_array($fileColumn, $columns)) {
                $fieldValue = 'NSP' !== $data[$fileColumn] ? $data[$fileColumn] : '';
                $fieldValue = trim($fieldValue, '"');
                switch ($fieldColumn) {
                    case 'typeLogement':
                        $fieldValue = $this->transformToTypeLogement($fieldValue);
                        break;
                    case 'localisationDansImmeuble':
                        $fieldValue = $this->transformToLocalisationDansImmeuble($fieldValue);
                        break;
                    case 'nomOccupant':
                    case 'prenomOccupant':
                        $fieldValue = 'Inconnu';
                        break;
                    case 'niveauInfestation':
                        $fieldValue = $this->transformToNiveauInfestation($fieldValue);
                        break;
                    case 'construitAvant1948':
                    case 'faitVisitePostTraitement':
                        $fieldValue = ('Oui' === $fieldValue) ? 1 : 0;
                        break;
                    case 'typeIntervention':
                        $fieldValue = $this->transformToTypeIntervention($fieldValue);
                        break;
                    case 'typeDiagnostic':
                        $fieldValue = $this->transformToTypeDiagnostic($fieldValue);
                        break;
                    case 'typeTraitement':
                        $fieldValue = $this->transformToTypeTraitement($fieldValue);
                        break;
                    case 'dateIntervention':
                    case 'dateVisitePostTraitement':
                    case 'closedAt':
                    case 'createdAt':
                        $fieldValue = $this->transformToDatetime($fieldValue);
                        break;
                    default:
                }
                $dataMapped[$fieldColumn] = $fieldValue;
            } else {
                $dataMapped[$fieldColumn] = null;
            }
        }

        return $dataMapped;
    }

    private function transformToTypeLogement(string $value): string
    {
        if ('Immeuble' === $value) {
            return 'appartement';
        } elseif ('Maison' === $value) {
            return 'maison';
        }

        return 'autre'; // autre ou null ?
    }

    private function transformToLocalisationDansImmeuble(string $value): ?string
    {
        if ('Logement' === $value) {
            return 'logement';
        } elseif ('Parties communes' === $value) {
            return 'communes';
        }

        return null;
    }

    private function transformToNiveauInfestation(string $value): int
    {
        if (str_contains($value, 'Nulle')) {
            return InfestationLevel::NULLE->value;
        } elseif (str_contains($value, 'Faible')) {
            return InfestationLevel::FAIBLE->value;
        } elseif (str_contains($value, 'Moyenne')) {
            return InfestationLevel::MOYENNE->value;
        } elseif (str_contains($value, 'Elevée')) {
            return InfestationLevel::ELEVEE->value;
        } elseif (str_contains($value, 'Très élevée')) {
            return InfestationLevel::TRES_ELEVEE->value;
        }

        return InfestationLevel::FAIBLE->value;
    }

    private function transformToTypeIntervention(string $value): ?string
    {
        if ('Diagnostic' === $value) {
            return 'diagnostic';
        } elseif ('Traitement' === $value) {
            return 'traitement';
        } elseif ('Diagnostic et Traitement' === $value) {
            return 'diagnostic-traitement';
        }

        return null;
    }

    private function transformToTypeDiagnostic(string $value): ?string
    {
        if ('Visuel' === $value) {
            return 'visuel';
        } elseif ('Canin' === $value) {
            return 'canin';
        } elseif ('Visuel et Canin' === $value) {
            return 'visuel-canin';
        }

        return null;
    }

    private function transformToTypeTraitement(string $value): array
    {
        $valueReturn = [];
        $value = strtolower($value);
        if (str_contains($value, 'vapeur')) {
            $valueReturn[] = 'vapeur';
        } elseif (str_contains($value, 'froid')) {
            $valueReturn[] = 'froid';
        } elseif (str_contains($value, 'biocide')
                || str_contains($value, 'fumigation')
                || str_contains($value, 'nebulisation')
        ) {
            $valueReturn[] = 'biocide';
        } elseif (str_contains($value, 'aspiration')) {
            $valueReturn[] = 'aspiration';
        } elseif (str_contains($value, 'tente')
                || str_contains($value, 'thermique')
        ) {
            $valueReturn[] = 'tente-chauffante';
        } elseif (str_contains($value, 'diatomee')
            || str_contains($value, 'arthropodes')
        ) {
            $valueReturn[] = 'mecanique';
        }

        return $valueReturn;
    }

    private function transformToDatetime(string $value): ?\DateTimeImmutable
    {
        $date = \DateTimeImmutable::createFromFormat('d/m/y', $value);
        if (false === $date) {
            $date = \DateTimeImmutable::createFromFormat('Y/m/d', $value);
        }
        if (false === $date) {
            $date = \DateTimeImmutable::createFromFormat('d/m/Y', $value);
        }

        return false !== $date ? $date : null;
    }
}
