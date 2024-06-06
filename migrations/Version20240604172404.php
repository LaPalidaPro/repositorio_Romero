<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240604172404 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE favorito (id INT AUTO_INCREMENT NOT NULL, usuario_id INT NOT NULL, cancion_id INT NOT NULL, INDEX IDX_881067C7DB38439E (usuario_id), INDEX IDX_881067C79B1D840F (cancion_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE favorito ADD CONSTRAINT FK_881067C7DB38439E FOREIGN KEY (usuario_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE favorito ADD CONSTRAINT FK_881067C79B1D840F FOREIGN KEY (cancion_id) REFERENCES cancion (id)');
        $this->addSql('ALTER TABLE album CHANGE generos_musicales generos_musicales JSON NOT NULL');
        $this->addSql('ALTER TABLE user CHANGE roles roles JSON NOT NULL, CHANGE foto foto VARCHAR(500) DEFAULT NULL');
        $this->addSql('ALTER TABLE messenger_messages CHANGE delivered_at delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE favorito DROP FOREIGN KEY FK_881067C7DB38439E');
        $this->addSql('ALTER TABLE favorito DROP FOREIGN KEY FK_881067C79B1D840F');
        $this->addSql('DROP TABLE favorito');
        $this->addSql('ALTER TABLE album CHANGE generos_musicales generos_musicales LONGTEXT NOT NULL COLLATE `utf8mb4_bin`');
        $this->addSql('ALTER TABLE messenger_messages CHANGE delivered_at delivered_at DATETIME DEFAULT \'NULL\' COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE user CHANGE roles roles LONGTEXT NOT NULL COLLATE `utf8mb4_bin`, CHANGE foto foto VARCHAR(500) DEFAULT \'NULL\'');
    }
}
