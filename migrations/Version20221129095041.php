<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20221129095041 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add tables intervention and message';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE intervention (id INT AUTO_INCREMENT NOT NULL, signalement_id INT NOT NULL, entreprise_id INT NOT NULL, accepted TINYINT(1) DEFAULT NULL, montant_estimation INT DEFAULT NULL, commentaire_estimation LONGTEXT DEFAULT NULL, accepted_by_entreprise_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', accepted_by_usager TINYINT(1) DEFAULT NULL, accepted_by_usager_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_D11814AB65C5E57E (signalement_id), INDEX IDX_D11814ABA4AEAFEA (entreprise_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE message (id INT AUTO_INCREMENT NOT NULL, signalement_id INT NOT NULL, entreprise_id INT NOT NULL, sender VARCHAR(50) NOT NULL, content LONGTEXT NOT NULL, INDEX IDX_B6BD307F65C5E57E (signalement_id), INDEX IDX_B6BD307FA4AEAFEA (entreprise_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE intervention ADD CONSTRAINT FK_D11814AB65C5E57E FOREIGN KEY (signalement_id) REFERENCES signalement (id)');
        $this->addSql('ALTER TABLE intervention ADD CONSTRAINT FK_D11814ABA4AEAFEA FOREIGN KEY (entreprise_id) REFERENCES entreprise (id)');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307F65C5E57E FOREIGN KEY (signalement_id) REFERENCES signalement (id)');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307FA4AEAFEA FOREIGN KEY (entreprise_id) REFERENCES entreprise (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE intervention DROP FOREIGN KEY FK_D11814AB65C5E57E');
        $this->addSql('ALTER TABLE intervention DROP FOREIGN KEY FK_D11814ABA4AEAFEA');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307F65C5E57E');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307FA4AEAFEA');
        $this->addSql('DROP TABLE intervention');
        $this->addSql('DROP TABLE message');
    }
}
