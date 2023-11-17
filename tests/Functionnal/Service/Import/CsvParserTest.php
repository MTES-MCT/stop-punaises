<?php

namespace App\Tests\Functional\Service\Import;

use App\Service\Import\CsvParser;
use Faker\Factory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CsvParserTest extends KernelTestCase
{
    private const FILEPATH = '/tmp/data.csv';

    private string $projectDir = '';

    protected function setUp(): void
    {
        self::bootKernel();
        $this->projectDir = static::getContainer()->getParameter('kernel.project_dir');
        $this->createRandomCSV($this->projectDir.'/tmp/data.csv');
    }

    public function testDefaultOptionsCsvParser(): void
    {
        $csvParser = new CsvParser();
        $this->assertEquals(1, $csvParser->getOptions()['first_line']);
        $this->assertEquals(';', $csvParser->getOptions()['delimiter']);
        $this->assertEquals('"', $csvParser->getOptions()['enclosure']);
        $this->assertEquals('\\', $csvParser->getOptions()['escape']);
    }

    public function testParseRandomCsv(): void
    {
        $options = ['first_line' => 0, 'delimiter' => ',', 'enclosure' => '"', 'escape' => '\\'];

        $csvParser = new CsvParser($options);
        $data = $csvParser->parse($this->projectDir.self::FILEPATH);

        $this->assertIsArray($data);
        $this->assertCount(11, $data);
        $this->assertContains('Lastname', $data[0], 'The first line does not contain Lastname as column value');
        $this->assertContains('Firstname', $data[0], 'The first line does not contain Firstname as column value');
        $this->assertContains('Email', $data[0], 'The first line  does not contain Email as column value');
    }

    public function testParseAsDict(): void
    {
        $options = ['first_line' => 0, 'delimiter' => ',', 'enclosure' => '"', 'escape' => '\\'];
        $csvParser = new CsvParser($options);
        $dataList = $csvParser->parseAsDict($this->projectDir.self::FILEPATH);

        foreach ($dataList as $dataItem) {
            if (\count($dataItem) > 1) {
                $this->assertArrayHasKey('Lastname', $dataItem);
                $this->assertArrayHasKey('Firstname', $dataItem);
                $this->assertArrayHasKey('Email', $dataItem);
            }
        }
    }

    public function testGetHeaders(): void
    {
        $options = ['first_line' => 0, 'delimiter' => ',', 'enclosure' => '"', 'escape' => '\\'];
        $csvParser = new CsvParser($options);
        $headers = $csvParser->getHeaders($this->projectDir.self::FILEPATH);

        $this->assertEquals(['Lastname', 'Firstname', 'Email'], $headers);
    }

    public function testGetContent(): void
    {
        $options = ['first_line' => 0, 'delimiter' => ',', 'enclosure' => '"', 'escape' => '\\'];
        $csvParser = new CsvParser($options);
        $content = $csvParser->getContent($this->projectDir.self::FILEPATH);

        $this->assertArrayHasKey('headers', $content);
        $this->assertArrayHasKey('rows', $content);
    }

    public function testSetDelimiter(): void
    {
        $csvParser = new CsvParser();
        $csvParser->setDelimiter(',');

        $this->assertEquals(',', $csvParser->getOptions()['delimiter']);
    }

    protected function tearDown(): void
    {
        unlink($this->projectDir.self::FILEPATH);
    }

    public function createRandomCSV($filepath, $line = 10): void
    {
        $faker = Factory::create();
        $list = [
            ['Lastname', 'Firstname', 'Email'],
        ];

        for ($i = 0; $i < $line; ++$i) {
            $row = [$faker->lastName(), $faker->firstName(), $faker->email()];
            $list[] = $row;
        }

        $fileresource = fopen($filepath, 'w');
        foreach ($list as $row) {
            fputcsv($fileresource, $row);
        }

        fclose($fileresource);
    }
}
