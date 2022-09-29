<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220929131426 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE signalement ADD agent_id INT DEFAULT NULL, DROP nom_agent_intervention');
        $this->addSql('ALTER TABLE signalement ADD CONSTRAINT FK_F4B551143414710B FOREIGN KEY (agent_id) REFERENCES employe (id)');
        $this->addSql('CREATE INDEX IDX_F4B551143414710B ON signalement (agent_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE signalement DROP FOREIGN KEY FK_F4B551143414710B');
        $this->addSql('DROP INDEX IDX_F4B551143414710B ON signalement');
        $this->addSql('ALTER TABLE signalement ADD nom_agent_intervention VARCHAR(100) DEFAULT NULL, DROP agent_id');
    }
}
