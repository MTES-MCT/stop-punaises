<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20221202102210 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add reminder and resolved dates to signalement';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE signalement ADD reminder_autotraitement_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', ADD resolved_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE signalement DROP reminder_autotraitement_at, DROP resolved_at');
    }
}
