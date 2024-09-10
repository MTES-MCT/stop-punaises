<?php

namespace App\Dto;

class DataTableResponse
{
    public function __construct(
        private int $draw = 0,
        private int $recordsTotal = 0,
        private int $recordsFiltered = 0,
        private array $data = [],
    ) {
    }

    public function getDraw(): int
    {
        return $this->draw;
    }

    public function getRecordsTotal(): int
    {
        return $this->recordsTotal;
    }

    public function getRecordsFiltered(): int
    {
        return $this->recordsFiltered;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function toArray(): array
    {
        return [
            'draw' => $this->draw,
            'recordsTotal' => $this->recordsTotal,
            'recordsFiltered' => $this->recordsFiltered,
            'data' => $this->data,
        ];
    }
}
