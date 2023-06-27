<?php

namespace App\Service\Signalement;

use App\Repository\SignalementRepository;

class ReferenceGenerator
{
    public function __construct(private SignalementRepository $signalementRepository)
    {
    }

    public function generate(?string $yearIn = null): string
    {
        $lastReference = $this->signalementRepository->findLastReference($yearIn);
        if (!empty($lastReference)) {
            list($year, $id) = explode('-', $lastReference['reference']);
            $id = (int) $id + 1;

            return $year.'-'.$id;
        }

        return $yearIn.'-'. 1;
    }
}
