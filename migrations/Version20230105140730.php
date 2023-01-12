<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230105140730 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add active column to event table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE event ADD active TINYINT(1) NOT NULL DEFAULT 1');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE event DROP active');
    }
}
