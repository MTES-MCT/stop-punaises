<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20231020131118 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add information to entreprises publiques';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE entreprise_publique CHANGE adresse adresse VARCHAR(255) DEFAULT NULL ADD is_intervention TINYINT(1) NOT NULL, ADD is_detection_canine TINYINT(1) NOT NULL, ADD is_pro_only TINYINT(1) DEFAULT NULL');
        $this->addSql('UPDATE entreprise_publique SET is_intervention = 1 WHERE is_intervention = \'\'');
        $this->addSql('UPDATE entreprise_publique SET is_detection_canine = 0 WHERE is_detection_canine = \'\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE entreprise_publique CHANGE adresse adresse VARCHAR(255) NOT NULL DROP is_intervention, DROP is_detection_canine, DROP is_pro_only');
    }
}
