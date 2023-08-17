<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230810133819 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add reminder_pending_entreprise_conclusion date field to intervention';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE intervention ADD reminder_pending_entreprise_conclusion_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE intervention DROP reminder_pending_entreprise_conclusion_at');
    }
}
