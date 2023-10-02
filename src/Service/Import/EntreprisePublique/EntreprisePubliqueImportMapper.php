<?php

namespace App\Service\Import\EntreprisePublique;

class EntreprisePubliqueImportMapper
{
    public function getMapping(): array
    {
        return [
            'Nom' => 'nom',
            'Adresse' => 'adresse',
            'URL' => 'url',
            'Telephone' => 'telephone',
            'Code postal' => 'codePostal',
            'Pays' => 'pays',
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
                if ('codePostal' === $fieldColumn) {
                    $fieldValue = str_pad($fieldValue, 5, '0', \STR_PAD_LEFT);
                }
                $dataMapped[$fieldColumn] = $fieldValue;
            } else {
                $dataMapped[$fieldColumn] = null;
            }
        }

        return $dataMapped;
    }
}
