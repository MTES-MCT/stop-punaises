<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230511081856 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'open territory 69';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('UPDATE territoire SET active = 1 WHERE zip = \'69\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('UPDATE territoire SET active = 0 WHERE zip = \'69\'');
    }
}
