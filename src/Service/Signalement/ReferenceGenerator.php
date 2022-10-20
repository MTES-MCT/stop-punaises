<?php

namespace App\Service\Signalement;

use App\Repository\SignalementRepository;

class ReferenceGenerator
{
    public function __construct(private SignalementRepository $signalementRepository)
    {
    }

    public function generate(): string
    {
        $lastReference = $this->signalementRepository->findLastReference();
        if (!empty($lastReference)) {
            list($year, $id) = explode('-', $lastReference['reference']);
            $id = (int) $id + 1;

            return $year.'-'.$id;
        }
        $year = (new \DateTime())->format('Y');

        return $year.'-'. 1;
    }
}
