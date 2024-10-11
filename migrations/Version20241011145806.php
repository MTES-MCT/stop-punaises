<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241011145806 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Set code_insee to NULL when it is 0';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("UPDATE `signalement` SET `code_insee` = NULL WHERE `code_insee` = '0'");
    }

    public function down(Schema $schema): void
    {
    }
}
