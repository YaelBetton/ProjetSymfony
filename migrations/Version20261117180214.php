<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Généré par make:migration puis adapté :
 * le champ texte jeu_video.developpeur devient une relation vers la nouvelle table developpeur,
 * sans perdre les développeurs déjà saisis.
 */
final class Version20261117180214 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout de l\'entité Developpeur (relation ManyToOne depuis JeuVideo)';
    }

    public function up(Schema $schema): void
    {
        // 1. Nouvelle table developpeur
        $this->addSql('CREATE TABLE developpeur (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(150) NOT NULL, pays VARCHAR(100) DEFAULT NULL, site_web VARCHAR(255) DEFAULT NULL, description LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 ENGINE = InnoDB');

        // 2. Reprise des données : un développeur par nom déjà présent dans jeu_video
        $this->addSql('INSERT INTO developpeur (nom, created_at) SELECT DISTINCT developpeur, NOW() FROM jeu_video');

        // 3. Clé étrangère, d'abord nullable pour pouvoir la remplir
        $this->addSql('ALTER TABLE jeu_video ADD developpeur_id INT DEFAULT NULL');
        $this->addSql('UPDATE jeu_video j INNER JOIN developpeur d ON d.nom = j.developpeur SET j.developpeur_id = d.id');

        // 4. Puis NOT NULL, suppression de l'ancienne colonne texte, contrainte + index
        $this->addSql('ALTER TABLE jeu_video MODIFY developpeur_id INT NOT NULL, DROP developpeur');
        $this->addSql('ALTER TABLE jeu_video ADD CONSTRAINT FK_4E22D9D484E66085 FOREIGN KEY (developpeur_id) REFERENCES developpeur (id)');
        $this->addSql('CREATE INDEX IDX_4E22D9D484E66085 ON jeu_video (developpeur_id)');
    }

    public function down(Schema $schema): void
    {
        // Retour au champ texte, en recopiant le nom du développeur
        $this->addSql('ALTER TABLE jeu_video ADD developpeur VARCHAR(255) DEFAULT NULL');
        $this->addSql('UPDATE jeu_video j INNER JOIN developpeur d ON d.id = j.developpeur_id SET j.developpeur = d.nom');
        $this->addSql('ALTER TABLE jeu_video MODIFY developpeur VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE jeu_video DROP FOREIGN KEY FK_4E22D9D484E66085');
        $this->addSql('DROP INDEX IDX_4E22D9D484E66085 ON jeu_video');
        $this->addSql('ALTER TABLE jeu_video DROP developpeur_id');
        $this->addSql('DROP TABLE developpeur');
    }
}
