<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class ConstructionAvant1948 extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('constructionAvant1948', [$this, 'format']),
        ];
    }

    public function format(bool|null $construitAvant1948 = null): string
    {
        if (null === $construitAvant1948) {
            return 'Non renseigné';
        }

        return $construitAvant1948 ? 'Oui' : 'Non';
    }
}
