<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221213042808 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE event (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, label VARCHAR(255) DEFAULT NULL, action_link VARCHAR(255) DEFAULT NULL, action_label VARCHAR(255) DEFAULT NULL, entity_name VARCHAR(255) DEFAULT NULL, entity_uuid VARCHAR(255) DEFAULT NULL, domain VARCHAR(255) NOT NULL, recipient VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE message_thread (id INT AUTO_INCREMENT NOT NULL, signalement_id INT DEFAULT NULL, entreprise_id INT DEFAULT NULL, uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_607D18C65C5E57E (signalement_id), INDEX IDX_607D18CA4AEAFEA (entreprise_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE message_thread ADD CONSTRAINT FK_607D18C65C5E57E FOREIGN KEY (signalement_id) REFERENCES signalement (id)');
        $this->addSql('ALTER TABLE message_thread ADD CONSTRAINT FK_607D18CA4AEAFEA FOREIGN KEY (entreprise_id) REFERENCES entreprise (id)');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307FA4AEAFEA');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307F65C5E57E');
        $this->addSql('DROP INDEX IDX_B6BD307FA4AEAFEA ON message');
        $this->addSql('DROP INDEX IDX_B6BD307F65C5E57E ON message');
        $this->addSql('ALTER TABLE message ADD messages_thread_id INT DEFAULT NULL, DROP signalement_id, DROP entreprise_id');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307FEC5AD08B FOREIGN KEY (messages_thread_id) REFERENCES message_thread (id)');
        $this->addSql('CREATE INDEX IDX_B6BD307FEC5AD08B ON message (messages_thread_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307FEC5AD08B');
        $this->addSql('ALTER TABLE message_thread DROP FOREIGN KEY FK_607D18C65C5E57E');
        $this->addSql('ALTER TABLE message_thread DROP FOREIGN KEY FK_607D18CA4AEAFEA');
        $this->addSql('DROP TABLE event');
        $this->addSql('DROP TABLE message_thread');
        $this->addSql('DROP INDEX IDX_B6BD307FEC5AD08B ON message');
        $this->addSql('ALTER TABLE message ADD signalement_id INT NOT NULL, ADD entreprise_id INT NOT NULL, DROP messages_thread_id');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307FA4AEAFEA FOREIGN KEY (entreprise_id) REFERENCES entreprise (id)');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307F65C5E57E FOREIGN KEY (signalement_id) REFERENCES signalement (id)');
        $this->addSql('CREATE INDEX IDX_B6BD307FA4AEAFEA ON message (entreprise_id)');
        $this->addSql('CREATE INDEX IDX_B6BD307F65C5E57E ON message (signalement_id)');
    }
}
