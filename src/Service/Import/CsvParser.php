<?php

namespace App\Service\Import;

class CsvParser
{
    public function __construct(
        private array $options = [
            'first_line' => 1,
            'delimiter' => ',',
            'enclosure' => '"',
            'escape' => '\\',
        ]
    ) {
    }

    public function parse(string $filepath): array
    {
        $rows = [];

        if (($fileResource = fopen($filepath, 'r')) !== false) {
            $i = 0;
            while (($row = fgetcsv(
                $fileResource,
                0,
                $this->options['delimiter'],
                $this->options['enclosure'],
                $this->options['escape']
            )) !== false) {
                if ($i >= $this->options['first_line']) {
                    $row = array_map('trim', $row);
                    $rows[] = $row;
                }
                ++$i;
            }
            fclose($fileResource);
        }

        return $rows;
    }

    public function parseAsDict(string $filepath): array
    {
        $content = $this->getContent($filepath);
        $dataList = [];
        foreach ($content['rows'] as $row) {
            $dataItem = [];
            foreach (str_getcsv($row,
                $this->options['delimiter'],
                $this->options['enclosure'],
                $this->options['escape']) as $key => $field) {
                $dataItem[$content['headers'][$key]] = $field;
            }
            $dataList[] = $dataItem;
        }

        return $dataList;
    }

    public function getHeaders(string $filepath): array
    {
        $rows = explode("\n", file_get_contents($filepath));

        $headers = str_getcsv(
            array_shift($rows),
            $this->options['delimiter'],
            $this->options['enclosure'],
            $this->options['escape']
        );

        return array_map('trim', $headers);
    }

    public function getContent(string $filepath): array
    {
        $rows = explode("\n", file_get_contents($filepath));

        $headers = str_getcsv(
            array_shift($rows),
            $this->options['delimiter'],
            $this->options['enclosure'],
            $this->options['escape']
        );

        return [
            'headers' => array_map('trim', $headers),
            'rows' => $rows,
        ];
    }

    public function getOptions(): array
    {
        return $this->options;
    }
}
