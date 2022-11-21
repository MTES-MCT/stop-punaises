<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20221121153509 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'territoire : delete est_actif (already replaced by active)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE territoire DROP est_actif');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE territoire ADD est_actif TINYINT(1) NOT NULL');
    }
}
