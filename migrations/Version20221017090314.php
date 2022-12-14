<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221017090314 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE employe (id INT AUTO_INCREMENT NOT NULL, entreprise_id INT NOT NULL, uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', nom VARCHAR(50) NOT NULL, prenom VARCHAR(50) NOT NULL, numero_certification VARCHAR(50) DEFAULT NULL, email VARCHAR(100) DEFAULT NULL, telephone VARCHAR(20) DEFAULT NULL, INDEX IDX_F804D3B9A4AEAFEA (entreprise_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE entreprise (id INT AUTO_INCREMENT NOT NULL, uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', nom VARCHAR(255) NOT NULL, numero_siret VARCHAR(50) NOT NULL, telephone VARCHAR(20) NOT NULL, numero_label VARCHAR(100) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE entreprise_territoire (entreprise_id INT NOT NULL, territoire_id INT NOT NULL, INDEX IDX_17F2C4BFA4AEAFEA (entreprise_id), INDEX IDX_17F2C4BFD0F97A8 (territoire_id), PRIMARY KEY(entreprise_id, territoire_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE signalement (id INT AUTO_INCREMENT NOT NULL, entreprise_id INT DEFAULT NULL, agent_id INT DEFAULT NULL, uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', adresse VARCHAR(255) NOT NULL, code_postal VARCHAR(10) NOT NULL, ville VARCHAR(255) NOT NULL, type_logement VARCHAR(20) NOT NULL, construit_avant1948 TINYINT(1) DEFAULT NULL, nom_occupant VARCHAR(50) NOT NULL, prenom_occupant VARCHAR(50) NOT NULL, telephone_occupant VARCHAR(20) DEFAULT NULL, email_occupant VARCHAR(100) DEFAULT NULL, type_intervention VARCHAR(10) NOT NULL, date_intervention DATE DEFAULT NULL, niveau_infestation SMALLINT DEFAULT NULL, type_traitement VARCHAR(50) DEFAULT NULL, nom_biocide VARCHAR(50) DEFAULT NULL, type_diagnostic VARCHAR(50) DEFAULT NULL, nombre_pieces_traitees SMALLINT DEFAULT NULL, delai_entre_interventions SMALLINT DEFAULT NULL, fait_visite_post_traitement TINYINT(1) DEFAULT NULL, date_visite_post_traitement DATE DEFAULT NULL, prix_facture_ht INT DEFAULT NULL, localisation_dans_immeuble VARCHAR(10) DEFAULT NULL, code_insee VARCHAR(10) NOT NULL, reference VARCHAR(100) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_F4B55114A4AEAFEA (entreprise_id), INDEX IDX_F4B551143414710B (agent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE territoire (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, zip VARCHAR(3) NOT NULL, est_actif TINYINT(1) NOT NULL, active TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, entreprise_id INT DEFAULT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, last_login DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', token VARCHAR(255) DEFAULT NULL, token_expired_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', status VARCHAR(255) NOT NULL, active TINYINT(1) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), UNIQUE INDEX UNIQ_8D93D649A4AEAFEA (entreprise_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE employe ADD CONSTRAINT FK_F804D3B9A4AEAFEA FOREIGN KEY (entreprise_id) REFERENCES entreprise (id)');
        $this->addSql('ALTER TABLE entreprise_territoire ADD CONSTRAINT FK_17F2C4BFA4AEAFEA FOREIGN KEY (entreprise_id) REFERENCES entreprise (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE entreprise_territoire ADD CONSTRAINT FK_17F2C4BFD0F97A8 FOREIGN KEY (territoire_id) REFERENCES territoire (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE signalement ADD CONSTRAINT FK_F4B55114A4AEAFEA FOREIGN KEY (entreprise_id) REFERENCES entreprise (id)');
        $this->addSql('ALTER TABLE signalement ADD CONSTRAINT FK_F4B551143414710B FOREIGN KEY (agent_id) REFERENCES employe (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649A4AEAFEA FOREIGN KEY (entreprise_id) REFERENCES entreprise (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE employe DROP FOREIGN KEY FK_F804D3B9A4AEAFEA');
        $this->addSql('ALTER TABLE entreprise_territoire DROP FOREIGN KEY FK_17F2C4BFA4AEAFEA');
        $this->addSql('ALTER TABLE entreprise_territoire DROP FOREIGN KEY FK_17F2C4BFD0F97A8');
        $this->addSql('ALTER TABLE signalement DROP FOREIGN KEY FK_F4B55114A4AEAFEA');
        $this->addSql('ALTER TABLE signalement DROP FOREIGN KEY FK_F4B551143414710B');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649A4AEAFEA');
        $this->addSql('DROP TABLE employe');
        $this->addSql('DROP TABLE entreprise');
        $this->addSql('DROP TABLE entreprise_territoire');
        $this->addSql('DROP TABLE signalement');
        $this->addSql('DROP TABLE territoire');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
