<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230425080606 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add date for intervention canceled by entreprise';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE event CHANGE active active TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE intervention ADD canceled_by_entreprise_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE event CHANGE active active TINYINT(1) DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE intervention DROP canceled_by_entreprise_at');
    }
}
