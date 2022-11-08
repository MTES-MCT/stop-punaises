<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221028080201 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE signalement ADD declarant VARCHAR(255) NOT NULL, ADD superficie SMALLINT DEFAULT NULL, ADD duree_infestation VARCHAR(20) DEFAULT NULL, ADD infestation_logements_voisins TINYINT(1) DEFAULT NULL, ADD piqures_existantes TINYINT(1) DEFAULT NULL, ADD piqures_confirmees TINYINT(1) DEFAULT NULL, ADD dejections_details JSON DEFAULT NULL, ADD oeufs_et_larves_details JSON DEFAULT NULL, ADD punaises_details JSON DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE signalement DROP declarant, DROP superficie, DROP duree_infestation, DROP infestation_logements_voisins, DROP piqures_existantes, DROP piqures_confirmees, DROP dejections_details, DROP oeufs_et_larves_details, DROP punaises_details');
    }
}
