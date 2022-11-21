<?php

namespace App\Twig;

use App\Entity\Enum\Declarant;
use App\Entity\Enum\InfestationLevel;
use App\Entity\Signalement;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AppExtension extends AbstractExtension
{
    public const PATTERN_REPLACE_PHONE_FR = '/^\+?33|\|?0033|\|+33 (0)|\D/';

    public function getFilters(): array
    {
        return [
            new TwigFilter('format_phone', [$this, 'formatPhone']),
            new TwigFilter('type_signalement', [$this, 'formatTypeSignalement']),
            new TwigFilter('label_infestation', [$this, 'formatLabelInfestation']),
            new TwigFilter('construction_avant_1948', [$this, 'formatConstructionAvant1948']),
        ];
    }

    public function formatPhone(?string $value): ?string
    {
        $value = preg_replace(self::PATTERN_REPLACE_PHONE_FR, '', $value);

        if (9 === \strlen($value)) {
            $value = str_pad($value, 10, '0', \STR_PAD_LEFT);
        }

        return trim(chunk_split($value, 2, ' '));
    }

    public function formatTypeSignalement(Signalement $signalement): string
    {
        if (Declarant::DECLARANT_ENTREPRISE == $signalement->getDeclarant()) {
            return 'Historique';
        } elseif ($signalement->isAutotraitement()) {
            return 'Auto-traitement';
        }

        return 'Usager';
    }

    public function formatLabelInfestation(?int $niveau = 0): string
    {
        return InfestationLevel::from($niveau)->label();
    }

    public function formatConstructionAvant1948(bool|null $construitAvant1948 = null): string
    {
        if (null === $construitAvant1948) {
            return 'Non renseigné';
        }

        return $construitAvant1948 ? 'Oui' : 'Non';
    }
}
