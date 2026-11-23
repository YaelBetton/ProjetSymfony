<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261123180205 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'TP6 : tables utilisateur et collect (collection de jeux vidéo des utilisateurs)';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE collect (id INT AUTO_INCREMENT NOT NULL, statut VARCHAR(30) NOT NULL, date_modif_statut DATETIME DEFAULT NULL, prix_achat NUMERIC(6, 2) DEFAULT NULL, date_achat DATE DEFAULT NULL, commentaire LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, utilisateur_id INT NOT NULL, jeu_video_id INT NOT NULL, UNIQUE INDEX uniq_collect_utilisateur_jeu (utilisateur_id, jeu_video_id), INDEX IDX_A40662F4FB88E14F (utilisateur_id), INDEX IDX_A40662F4695B6720 (jeu_video_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 ENGINE = InnoDB');
        $this->addSql('CREATE TABLE utilisateur (id INT AUTO_INCREMENT NOT NULL, prenom VARCHAR(100) NOT NULL, nom VARCHAR(100) NOT NULL, pseudonyme VARCHAR(30) NOT NULL, mail VARCHAR(180) NOT NULL, date_naissance DATE NOT NULL, image_profil VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_1D1C63B31FE3BDAF (pseudonyme), UNIQUE INDEX UNIQ_1D1C63B35126AC48 (mail), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 ENGINE = InnoDB');
        $this->addSql('ALTER TABLE collect ADD CONSTRAINT FK_A40662F4FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE collect ADD CONSTRAINT FK_A40662F4695B6720 FOREIGN KEY (jeu_video_id) REFERENCES jeu_video (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE collect DROP FOREIGN KEY FK_A40662F4FB88E14F');
        $this->addSql('ALTER TABLE collect DROP FOREIGN KEY FK_A40662F4695B6720');
        $this->addSql('DROP TABLE collect');
        $this->addSql('DROP TABLE utilisateur');
    }
}
