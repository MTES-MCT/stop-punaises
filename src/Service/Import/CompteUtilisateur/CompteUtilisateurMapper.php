<?php

namespace App\Service\Import\CompteUtilisateur;

class CompteUtilisateurMapper
{
    public const COLUMN_ENTREPRISE = 'Entreprise';
    public const COLUMN_TELEPHONE = 'telephone';
    public const COLUMN_EMAIL_ADRESSE = 'Adresse email du gestionnaire';
    public const COLUMN_SIRET = 'Siret';
    public const COLUMN_DEPARTEMENT_ACTIVITE = 'Departement activité entreprise';
    public const COLUMN_LABEL = 'Label';

    public function getMapping(): array
    {
        return [
            self::COLUMN_ENTREPRISE => 'entreprise_nom',
            self::COLUMN_TELEPHONE => 'entreprise_telephone',
            self::COLUMN_EMAIL_ADRESSE => 'entreprise_email',
            self::COLUMN_SIRET => 'entreprise_numero_siret',
            self::COLUMN_DEPARTEMENT_ACTIVITE => 'entreprise_territoires_zip',
            self::COLUMN_LABEL => 'entreprise_numero_label',
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
                $fieldValue = trim($data[$fileColumn], '"');
                $fieldValue = trim($data[$fileColumn], ' "');
                if ('entreprise_territoires_zip' === $fieldColumn) {
                    $fieldValue = array_map('trim', explode(',', $fieldValue));
                }

                $dataMapped[$fieldColumn] = $fieldValue;
            } else {
                $dataMapped[$fieldColumn] = null;
            }
        }

        return $dataMapped;
    }
}
