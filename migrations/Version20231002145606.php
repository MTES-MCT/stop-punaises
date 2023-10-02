<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20231002145606 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add entity entreprise_publique';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE entreprise_publique (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, adresse VARCHAR(255) NOT NULL, url VARCHAR(255) DEFAULT NULL, telephone VARCHAR(255) DEFAULT NULL, code_postal VARCHAR(5) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE entreprise_publique');
    }
}
