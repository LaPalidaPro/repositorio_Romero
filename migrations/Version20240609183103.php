<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240609183103 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE album CHANGE generos_musicales generos_musicales JSON NOT NULL');
        $this->addSql('ALTER TABLE user ADD eventos_id INT DEFAULT NULL, CHANGE roles roles JSON NOT NULL, CHANGE foto foto VARCHAR(500) DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D6497F243861 FOREIGN KEY (eventos_id) REFERENCES evento (id)');
        $this->addSql('CREATE INDEX IDX_8D93D6497F243861 ON user (eventos_id)');
        $this->addSql('ALTER TABLE messenger_messages CHANGE delivered_at delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE album CHANGE generos_musicales generos_musicales LONGTEXT NOT NULL COLLATE `utf8mb4_bin`');
        $this->addSql('ALTER TABLE messenger_messages CHANGE delivered_at delivered_at DATETIME DEFAULT \'NULL\' COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D6497F243861');
        $this->addSql('DROP INDEX IDX_8D93D6497F243861 ON user');
        $this->addSql('ALTER TABLE user DROP eventos_id, CHANGE roles roles LONGTEXT NOT NULL COLLATE `utf8mb4_bin`, CHANGE foto foto VARCHAR(500) DEFAULT \'NULL\'');
    }
}
