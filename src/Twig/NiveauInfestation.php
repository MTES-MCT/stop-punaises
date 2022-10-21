<?php

namespace App\Twig;

use App\Entity\Enum\InfestationLevel;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class NiveauInfestation extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('label_infestation', [$this, 'format']),
        ];
    }

    public function format(?int $niveau = 0): string
    {
        return InfestationLevel::from($niveau)->label();
    }
}
