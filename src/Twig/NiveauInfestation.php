<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class NiveauInfestation extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('niveauInfestation', [$this, 'format']),
        ];
    }

    public function format(int $niveau = 0): string
    {
        switch ($niveau) {
            case 0:
                return '0 - Nulle';
            case 1:
                return '1 - Faible';
            case 2:
                return '2 - Moyenne';
            case 3:
                return '3 - Elevée';
            case 4:
            default:
                return '4 - Très élevée';
        }
    }
}
