<?php

namespace App\Service\Import\EntreprisePublique;

class EntreprisePubliqueImportMapper
{
    public function getMapping(): array
    {
        return [
            'zip' => 'zip',
            'nom' => 'nom',
            'adresse' => 'adresse',
            'url' => 'url',
            'telephone' => 'telephone',
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
                if ('zip' === $fieldColumn) {
                    $fieldValue = str_pad($fieldValue, 2, '0', \STR_PAD_LEFT);
                }
                $dataMapped[$fieldColumn] = $fieldValue;
            } else {
                $dataMapped[$fieldColumn] = null;
            }
        }

        return $dataMapped;
    }
}
