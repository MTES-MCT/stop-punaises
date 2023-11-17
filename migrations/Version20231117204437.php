<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20231117204437 extends AbstractMigration
{
    private const TERRITOIRES = [
        '02', '2A', '2B', '04', '06',
        '07', '08', '09', '17', '18',
        '19', '21', '23', '24', '28',
        '29', '31', '33', '34', '35',
        '37', '38', '40', '42', '43',
        '44', '45', '46', '47', '49',
        '50', '51', '52', '54', '55',
        '58', '59', '60', '62', '64',
        '66', '70', '71', '72', '73',
        '81', '84', '87', '89',
    ];

    public function getDescription(): string
    {
        return 'Open territories';
    }

    public function up(Schema $schema): void
    {
        foreach (self::TERRITOIRES as $zipCode) {
            $this->addSql('UPDATE territoire SET active = 1 WHERE zip = :zip', ['zip' => $zipCode]);
        }
    }

    public function down(Schema $schema): void
    {
        foreach (self::TERRITOIRES as $zipCode) {
            $this->addSql('UPDATE territoire SET active = 0 WHERE zip = :zip', ['zip' => $zipCode]);
        }
    }
}
