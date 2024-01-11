<?php

namespace App\Dto;

use Symfony\Component\HttpFoundation\Request;

class DataTableRequest
{
    private ?string $orderColumn = null;
    private ?string $orderDir = null;
    private array $search = [];

    public function __construct(
        private ?int $draw = null,
        private int $start = 0,
        private int $length = 0,
        private ?array $columns = null,
        private ?array $order = null,
    ) {
        $this->orderColumn = $this->order[0]['column'] ?? 0;
        $this->orderDir = $this->order[0]['dir'] ?? 'ASC';

        if (!empty($this->columns)) {
            foreach ($this->columns as $item) {
                $this->search[$item['data']] = $item['search']['value'] ?? null;
            }
        }
    }

    public static function buildFromRequest(Request $request): self
    {
        return new self(
            draw: $request->get('draw'),
            start: $request->get('start'),
            length: $request->get('length'),
            columns: $request->get('columns'),
            order: $request->get('order'),
        );
    }

    public function getDraw(): int
    {
        return $this->draw;
    }

    public function getStart(): int
    {
        return $this->start;
    }

    public function getLength(): int
    {
        return $this->length;
    }

    public function getColumns(): array
    {
        return $this->columns;
    }

    public function getOrder(): array
    {
        return $this->order;
    }

    public function getOrderColumn(): string
    {
        return $this->orderColumn;
    }

    public function getOrderDirection(): string
    {
        return $this->orderDir;
    }

    public function getSearchByIndex(int $index): string
    {
        return $this->search[$index];
    }
}
