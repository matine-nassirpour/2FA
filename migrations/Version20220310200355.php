<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220310200355 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE users (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles CLOB NOT NULL --(DC2Type:json)
        , password VARCHAR(255) NOT NULL, registered_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , account_must_be_verified_before DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , registration_token VARCHAR(255) DEFAULT NULL, is_verified BOOLEAN NOT NULL, account_verified_at DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
        , forgot_password_token VARCHAR(255) DEFAULT NULL, forgot_password_token_requested_at DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
        , forgot_password_token_must_be_verified_before DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
        , forgot_password_token_verified_at DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
        , auth_code VARCHAR(255) DEFAULT NULL)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E9E7927C74 ON users (email)');
        $this->addSql('DROP TABLE auth_log');
        $this->addSql('DROP TABLE user');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE auth_log (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, auth_attempt_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , user_ip VARCHAR(255) DEFAULT NULL COLLATE BINARY, email_entered VARCHAR(255) NOT NULL COLLATE BINARY, is_successful_auth BOOLEAN NOT NULL, start_of_black_listing DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
        , end_of_black_listing DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
        , is_remember_me_auth BOOLEAN NOT NULL, deauthenticated_at DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
        )');
        $this->addSql('CREATE TABLE user (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, email VARCHAR(180) NOT NULL COLLATE BINARY, roles CLOB NOT NULL COLLATE BINARY --(DC2Type:json)
        , password VARCHAR(255) NOT NULL COLLATE BINARY, auth_code VARCHAR(255) DEFAULT NULL COLLATE BINARY)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON user (email)');
        $this->addSql('DROP TABLE users');
    }
}
