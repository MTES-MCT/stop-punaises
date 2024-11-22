<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class CartographieRequest
{
    #[Assert\NotBlank]
    public readonly \DateTimeImmutable $date;

    #[Assert\NotBlank]
    public readonly ?float $swLat;

    #[Assert\NotBlank]
    public readonly ?float $swLng;

    #[Assert\NotBlank]
    public readonly ?float $neLat;

    #[Assert\NotBlank]
    public readonly ?float $neLng;

    public function __construct(
        ?float $swLat,
        ?float $swLng,
        ?float $neLat,
        ?float $neLng,
        \DateTimeImmutable $date,
    ) {
        $this->swLat = $swLat;
        $this->swLng = $swLng;
        $this->neLat = $neLat;
        $this->neLng = $neLng;
        $this->date = $date;
    }

    public function getDate(): string
    {
        return $this->date->format('Y-m-d H:i:s');
    }

    public function getSwLat(): ?float
    {
        return $this->swLat;
    }

    public function getSwLng(): ?float
    {
        return $this->swLng;
    }

    public function getNeLat(): ?float
    {
        return $this->neLat;
    }

    public function getNeLng(): ?float
    {
        return $this->neLng;
    }
}
