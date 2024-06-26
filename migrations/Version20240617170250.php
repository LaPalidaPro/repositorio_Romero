<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240617170250 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE album (id INT AUTO_INCREMENT NOT NULL, artista_id INT NOT NULL, fecha_lanzamiento DATETIME NOT NULL, num_pistas INT NOT NULL, duracion_total VARCHAR(255) NOT NULL, generos_musicales JSON NOT NULL, foto_portada VARCHAR(500) NOT NULL, nombre VARCHAR(255) NOT NULL, INDEX IDX_39986E43AEB0CF13 (artista_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE artista (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, nombre VARCHAR(255) NOT NULL, ano_debut DATETIME NOT NULL, pais_origen VARCHAR(255) NOT NULL, biografia VARCHAR(500) NOT NULL, img_artista VARCHAR(500) NOT NULL, INDEX IDX_9B6AF156A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE cancion (id INT AUTO_INCREMENT NOT NULL, artista_id INT NOT NULL, album_id INT NOT NULL, titulo VARCHAR(255) NOT NULL, duracion VARCHAR(255) NOT NULL, genero_musical VARCHAR(255) NOT NULL, fecha_lanzamiento DATETIME NOT NULL, numero_reproducciones INT NOT NULL, INDEX IDX_E4620FA0AEB0CF13 (artista_id), INDEX IDX_E4620FA01137ABCF (album_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE evento (id INT AUTO_INCREMENT NOT NULL, nombre VARCHAR(255) NOT NULL, fecha_hora DATETIME NOT NULL, lugar VARCHAR(255) NOT NULL, precio NUMERIC(10, 0) NOT NULL, aforo INT NOT NULL, descripcion VARCHAR(500) NOT NULL, categoria VARCHAR(255) NOT NULL, cartel VARCHAR(500) NOT NULL, numero_asistentes INT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE favorito (id INT AUTO_INCREMENT NOT NULL, usuario_id INT NOT NULL, cancion_id INT NOT NULL, INDEX IDX_881067C7DB38439E (usuario_id), INDEX IDX_881067C79B1D840F (cancion_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE publicidad (id INT AUTO_INCREMENT NOT NULL, imagen VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, eventos_id INT DEFAULT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, nombre VARCHAR(255) NOT NULL, apellidos VARCHAR(255) NOT NULL, fecha_registro DATETIME NOT NULL, foto VARCHAR(500) DEFAULT NULL, sid VARCHAR(500) NOT NULL, INDEX IDX_8D93D6497F243861 (eventos_id), UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE album ADD CONSTRAINT FK_39986E43AEB0CF13 FOREIGN KEY (artista_id) REFERENCES artista (id)');
        $this->addSql('ALTER TABLE artista ADD CONSTRAINT FK_9B6AF156A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE cancion ADD CONSTRAINT FK_E4620FA0AEB0CF13 FOREIGN KEY (artista_id) REFERENCES artista (id)');
        $this->addSql('ALTER TABLE cancion ADD CONSTRAINT FK_E4620FA01137ABCF FOREIGN KEY (album_id) REFERENCES album (id)');
        $this->addSql('ALTER TABLE favorito ADD CONSTRAINT FK_881067C7DB38439E FOREIGN KEY (usuario_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE favorito ADD CONSTRAINT FK_881067C79B1D840F FOREIGN KEY (cancion_id) REFERENCES cancion (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D6497F243861 FOREIGN KEY (eventos_id) REFERENCES evento (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE album DROP FOREIGN KEY FK_39986E43AEB0CF13');
        $this->addSql('ALTER TABLE artista DROP FOREIGN KEY FK_9B6AF156A76ED395');
        $this->addSql('ALTER TABLE cancion DROP FOREIGN KEY FK_E4620FA0AEB0CF13');
        $this->addSql('ALTER TABLE cancion DROP FOREIGN KEY FK_E4620FA01137ABCF');
        $this->addSql('ALTER TABLE favorito DROP FOREIGN KEY FK_881067C7DB38439E');
        $this->addSql('ALTER TABLE favorito DROP FOREIGN KEY FK_881067C79B1D840F');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D6497F243861');
        $this->addSql('DROP TABLE album');
        $this->addSql('DROP TABLE artista');
        $this->addSql('DROP TABLE cancion');
        $this->addSql('DROP TABLE evento');
        $this->addSql('DROP TABLE favorito');
        $this->addSql('DROP TABLE publicidad');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
