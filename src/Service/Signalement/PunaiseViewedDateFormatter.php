<?php

namespace App\Service\Signalement;

class PunaiseViewedDateFormatter
{
    public const FORMAT_DATE = 'Y-m-d';
    public const FORMAT_HOUR = 'H:i:s';

    public static function format(
        \DateTimeInterface $dateViewedAt,
        \DateTimeInterface $timeViewedAt,
    ): \DateTimeImmutable {
        return new \DateTimeImmutable(
            \sprintf('%s %s', $dateViewedAt->format(self::FORMAT_DATE), $timeViewedAt->format(self::FORMAT_HOUR))
        );
    }
}
