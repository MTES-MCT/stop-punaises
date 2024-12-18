<?php

namespace App\Twig;

use App\Entity\Enum\InfestationLevel;
use App\Entity\Enum\PlaceType;
use App\Entity\Enum\SignalementType;
use App\Entity\Signalement;
use App\Service\Signalement\StatusProvider;
use App\Utils\FileHelper;
use Symfony\Bundle\SecurityBundle\Security;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AppExtension extends AbstractExtension
{
    public const PATTERN_REPLACE_PHONE_FR = '/^\+?33|\|?0033|\|+33 (0)|\D/';

    public function __construct(
        private Security $security,
    ) {
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('format_phone', [$this, 'formatPhone']),
            new TwigFilter('statut_signalement', [$this, 'formatStatutSignalement']),
            new TwigFilter('type_signalement', [$this, 'formatTypeSignalement']),
            new TwigFilter('label_infestation', [$this, 'formatLabelInfestation']),
            new TwigFilter('construction_avant_1948', [$this, 'formatConstructionAvant1948']),
            new TwigFilter('array_to_string', [$this, 'formatArrayToString']),
            new TwigFilter('reference_sortable', [$this, 'formatSortableReference']),
            new TwigFilter('signalement_type', [$this, 'formatSignalementType']),
            new TwigFilter('place_type', [$this, 'formatPlaceType']),
            new TwigFilter('format_bytes', [$this, 'formatBytes']),
        ];
    }

    public function formatPhone(?string $value): ?string
    {
        if (empty($value)) {
            return '';
        }

        $value = preg_replace(self::PATTERN_REPLACE_PHONE_FR, '', $value);

        if (9 === \strlen($value)) {
            $value = str_pad($value, 10, '0', \STR_PAD_LEFT);
        }

        return trim(chunk_split($value, 2, ' '));
    }

    public function formatStatutSignalement(Signalement $signalement): string
    {
        $statutFormat = StatusProvider::get($this->security, $signalement);

        return '<p class="fr-badge fr-badge--'.$statutFormat['badge']
                .' fr-badge--no-icon">'
                .$statutFormat['label'].'</p>';
    }

    public function formatTypeSignalement(Signalement $signalement): string
    {
        if ($signalement->isLogementSocial()) {
            return 'Logement social';
        }

        if ($signalement->isAutotraitement()) {
            return 'Auto-traitement';
        }

        return 'A traiter';
    }

    public function formatLabelInfestation(?int $niveau = 0): string
    {
        if (empty($niveau) && 0 !== $niveau) {
            return 'Non communiqué';
        }

        return InfestationLevel::from($niveau)->label();
    }

    public function formatSignalementType(SignalementType $type): string
    {
        return $type->label();
    }

    public function formatPlaceType(PlaceType $type): string
    {
        return $type->label();
    }

    public function formatConstructionAvant1948(?bool $construitAvant1948 = null): string
    {
        if (null === $construitAvant1948) {
            return 'Non renseigné';
        }

        return $construitAvant1948 ? 'Oui' : 'Non';
    }

    public function formatArrayToString(?array $listData): string
    {
        $str = '';
        foreach ($listData as $index => $data) {
            if ('' != $str) {
                $str .= ', ';
            }
            $str .= $data;
        }

        return $str;
    }

    public function formatSortableReference(?string $reference = ''): string
    {
        if (empty($reference)) {
            return '';
        }

        $referenceSplit = explode('-', $reference);
        if (\count($referenceSplit) < 2) {
            return $reference;
        }

        return $referenceSplit[0].'-'.str_pad($referenceSplit[1], 10, 0, \STR_PAD_LEFT);
    }

    public function formatBytes($bytes, $precision = 2): string
    {
        return FileHelper::fileSizeFormatter($bytes, $precision);
    }
}
