<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20221121100124 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add territoire field to Signalement';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE signalement ADD territoire_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE signalement ADD CONSTRAINT FK_F4B55114D0F97A8 FOREIGN KEY (territoire_id) REFERENCES territoire (id)');
        $this->addSql('CREATE INDEX IDX_F4B55114D0F97A8 ON signalement (territoire_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE signalement DROP FOREIGN KEY FK_F4B55114D0F97A8');
        $this->addSql('DROP INDEX IDX_F4B55114D0F97A8 ON signalement');
        $this->addSql('ALTER TABLE signalement DROP territoire_id');
    }
}
