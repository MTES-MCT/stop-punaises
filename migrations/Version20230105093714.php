<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230105093714 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add user_id_excluded to Event entity';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE event ADD user_id_excluded INT DEFAULT NULL, CHANGE user_id user_id INT DEFAULT NULL COMMENT \'null if usager, -1 if admin, 0 if all, id if entreprise\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE event DROP user_id_excluded, CHANGE user_id user_id INT DEFAULT NULL');
    }
}
