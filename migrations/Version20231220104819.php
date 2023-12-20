<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20231220104819 extends AbstractMigration
{
    private const TERRITOIRES = [
        '01', '12', '48',
    ];

    public function getDescription(): string
    {
        return 'Open new territories 01, 12, 48';
    }

    public function up(Schema $schema): void
    {
        $territoires = $this->connection->fetchAssociative('SELECT * FROM territoire');
        $this->skipIf(!$territoires, 'Territoire table does not exist yet, please execute migration manually');

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
