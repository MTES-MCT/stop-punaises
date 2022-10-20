<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AppExtension extends AbstractExtension
{
    public const PATTERN_REPLACE_PHONE_FR = '/^\+?33|\|?0033|\|+33 (0)|\D/';

    public function getFilters()
    {
        return [
            new TwigFilter('format_phone', [$this, 'formatPhone']),
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
}
