<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20221209091935 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add date for resolve by entreprise';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE intervention ADD resolved_by_entreprise_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE intervention DROP resolved_by_entreprise_at');
    }
}
