<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20221202145122 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'rename field accepted_by_at to choice_by_at on interventions';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE intervention ADD choice_by_entreprise_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', ADD choice_by_usager_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', DROP accepted_by_entreprise_at, DROP accepted_by_usager_at');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE intervention ADD accepted_by_entreprise_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', ADD accepted_by_usager_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', DROP choice_by_entreprise_at, DROP choice_by_usager_at');
    }
}
