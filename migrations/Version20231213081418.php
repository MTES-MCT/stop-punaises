<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20231213081418 extends AbstractMigration
{
    private const TERRITOIRES = [
        '03', '05', '10', '11',
        '14', '15', '16', '22',
        '25', '26', '27', '30',
        '32', '36', '39', '41',
        '53', '56', '57', '61',
        '65', '68', '74', '76',
        '77', '78', '79', '80',
        '82', '83', '85', '86',
        '88', '90', '91', '94',
        '95',
    ];

    public function getDescription(): string
    {
        return 'Open new territories';
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
