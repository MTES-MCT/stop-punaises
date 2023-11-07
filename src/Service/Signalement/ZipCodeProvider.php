<?php

namespace App\Service\Signalement;

class ZipCodeProvider
{
    public function getByCodePostal($codePostal): string
    {
        $zipCode = substr($codePostal, 0, 2);
        if ('97' == $zipCode) {
            $zipCode = substr($codePostal, 0, 3);
        }
        if ('20' == $zipCode) {
            $zipCode = $codePostal < 20200 ? '2A' : '2B';
        }

        return $zipCode;
    }
}
