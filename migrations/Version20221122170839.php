<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20221122170839 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add new fields to signalement (locataire info)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE signalement ADD locataire TINYINT(1) DEFAULT NULL, ADD nom_proprietaire VARCHAR(100) DEFAULT NULL, ADD logement_social TINYINT(1) DEFAULT NULL, ADD allocataire TINYINT(1) DEFAULT NULL, ADD numero_allocataire VARCHAR(50) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE signalement DROP locataire, DROP nom_proprietaire, DROP logement_social, DROP allocataire, DROP numero_allocataire');
    }
}
