<?php

namespace App\Service\Signalement;

class ZipCodeService
{
    public function __construct()
    {
    }

    public function getByCodePostal($codePostal): string
    {
        $zipCode = substr($codePostal, 0, 2);
        if ('97' == $zipCode) {
            $zipCode = substr($codePostal, 0, 3);
        }

        return $zipCode;
    }
}
