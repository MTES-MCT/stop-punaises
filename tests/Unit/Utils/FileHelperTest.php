<?php

namespace App\Tests\Unit\Utils;

use App\Utils\FileHelper;
use PHPUnit\Framework\TestCase;

class FileHelperTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testFileSizeFormatter($data, $expectedResult): void
    {
        $formattedData = FileHelper::fileSizeFormatter($data[0], $data[1]);

        $this->assertEquals($expectedResult, $formattedData);
    }

    public function provideData(): \Generator
    {
        yield '1024 sans décimale' => [
            [1024, 0],
            '1 Ko',
        ];

        yield '2000 sans décimale' => [
            [2000, 0],
            '2 Ko',
        ];

        yield '2000 avec 2 décimales' => [
            [2000, 2],
            '1.95 Ko',
        ];

        yield "367\u{202f}127 avec 1 décimale" => [
            [367127, 1],
            '358.5 Ko',
        ];
        yield "4\u{202f}132\u{202f}973 avec 3 décimales" => [
            [4132973, 3],
            '3.942 Mo',
        ];
    }
}
