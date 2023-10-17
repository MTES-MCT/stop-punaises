<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20231016145131 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add columns to signalement in order to manage ERP and Transport signalements';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE signalement ADD type VARCHAR(255) NOT NULL, ADD punaises_viewed_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', ADD place_type VARCHAR(255) DEFAULT NULL, ADD is_place_avertie TINYINT(1) DEFAULT NULL, ADD autres_informations LONGTEXT DEFAULT NULL, ADD nom_declarant VARCHAR(50) DEFAULT NULL, ADD prenom_declarant VARCHAR(50) DEFAULT NULL, ADD email_declarant VARCHAR(100) DEFAULT NULL');
        $this->addSql('UPDATE signalement SET type = \'TYPE_LOGEMENT\' WHERE type = \'\'');
        $this->addSql('ALTER TABLE signalement CHANGE nom_occupant nom_occupant VARCHAR(50) DEFAULT NULL, CHANGE prenom_occupant prenom_occupant VARCHAR(50) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE signalement DROP type, DROP punaises_viewed_at, DROP place_type, DROP is_place_avertie, DROP autres_informations, DROP nom_declarant, DROP prenom_declarant, DROP email_declarant');
        $this->addSql('ALTER TABLE signalement CHANGE nom_occupant nom_occupant VARCHAR(50) NOT NULL, CHANGE prenom_occupant prenom_occupant VARCHAR(50) NOT NULL');
    }
}
