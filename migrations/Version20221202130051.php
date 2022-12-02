<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20221202130051 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add switched_traitement and closed dates to signalement';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE signalement ADD switched_traitement_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', ADD closed_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE signalement DROP switched_traitement_at, DROP closed_at');
    }
}
