<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20221121111156 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'set several Signalement fields to nullable';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE signalement CHANGE adresse adresse VARCHAR(255) DEFAULT NULL, CHANGE ville ville VARCHAR(255) DEFAULT NULL, CHANGE type_logement type_logement VARCHAR(20) DEFAULT NULL, CHANGE code_insee code_insee VARCHAR(10) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE signalement CHANGE adresse adresse VARCHAR(255) NOT NULL, CHANGE ville ville VARCHAR(255) NOT NULL, CHANGE type_logement type_logement VARCHAR(20) NOT NULL, CHANGE code_insee code_insee VARCHAR(10) NOT NULL');
    }
}
