<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\Uid\Uuid;

final class Version20240319132426 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add UUID to user and generate it for existing users.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user ADD uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\'');

        $users = $this->connection->fetchAllAssociative(
            'SELECT id FROM user'
        );

        foreach ($users as $user) {
            $id = $user['id'];
            $code = Uuid::v4()->toRfc4122();

            $this->addSql(
                'UPDATE user SET uuid = :code WHERE id = :id',
                ['code' => $code, 'id' => $id]
            );
        }
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user DROP uuid');
    }
}
