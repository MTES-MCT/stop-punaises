<?php

namespace App\Service\Import;

class CsvWriter
{
    private mixed $fileResource;

    public function __construct(
        private string $filepath,
        private array $header = [],
        private array $options = [
            'first_line' => 1,
            'delimiter' => ',',
            'enclosure' => '"',
            'escape' => '\\',
        ]
    ) {
        if (file_exists($this->filepath)) {
            unlink($this->filepath);
        }

        if (!empty($this->header)) {
            $this->writeRow($this->header);
        }
    }

    public function writeRow(array $row): void
    {
        $this->fileResource = fopen($this->filepath, 'a');
        fputcsv(
            $this->fileResource,
            $row,
            $this->options['delimiter'],
            $this->options['enclosure'],
            $this->options['escape'],
        );
    }

    public function getHeader(): array
    {
        return array_filter($this->header);
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function close(): void
    {
        fclose($this->fileResource);
    }
}
