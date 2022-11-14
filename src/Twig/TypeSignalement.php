<?php

namespace App\Twig;

use App\Entity\Enum\Declarant;
use App\Entity\Signalement;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class TypeSignalement extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('type_signalement', [$this, 'format']),
        ];
    }

    public function format(Signalement $signalement): string
    {
        if (Declarant::DECLARANT_ENTREPRISE == $signalement->getDeclarant()) {
            return 'Historique';
        } elseif ($signalement->isAutotraitement()) {
            return 'Auto-traitement';
        }

        return 'Usager';
    }
}
