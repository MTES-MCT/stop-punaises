<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20231016193551 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'On signalement, nom_occupant and prenom_occupant are now nullable';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE signalement CHANGE nom_occupant nom_occupant VARCHAR(50) DEFAULT NULL, CHANGE prenom_occupant prenom_occupant VARCHAR(50) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE signalement CHANGE nom_occupant nom_occupant VARCHAR(50) NOT NULL, CHANGE prenom_occupant prenom_occupant VARCHAR(50) NOT NULL');
    }
}
