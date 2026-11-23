<?php

namespace App\DataFixtures;

use App\Entity\Collect;
use App\Entity\JeuVideo;
use App\Entity\Utilisateur;
use App\Enum\StatutJeuEnum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * TP6 — Utilisateurs et collections de jeux vidéo.
 *
 *  - 6 utilisateurs ;
 *  - 4 utilisateurs ont plus de 5 jeux dans leur collection ;
 *  - 1 utilisateur (Hugo) n'a aucun jeu.
 *
 * Chargement complet (purge la BDD) : php bin/console doctrine:fixtures:load
 * Ajout des seules collections, sans toucher aux jeux existants :
 *     php bin/console doctrine:fixtures:load --group=collection --append
 */
class CollectionFixtures extends Fixture implements OrderedFixtureInterface, FixtureGroupInterface
{
    /**
     * Chargée après AppFixtures (ordre 0 par défaut), qui crée les jeux.
     * On n'utilise pas DependentFixtureInterface : avec --group=collection, la dépendance
     * rechargerait AppFixtures et dupliquerait tous les jeux en mode --append.
     */
    public function getOrder(): int
    {
        return 10;
    }

    public static function getGroups(): array
    {
        return ['collection'];
    }

    public function load(ObjectManager $manager): void
    {
        // Les jeux sont retrouvés par leur titre (fonctionne aussi en mode --append)
        $jeux = [];
        foreach ($manager->getRepository(JeuVideo::class)->findAll() as $jeu) {
            $jeux[$jeu->getTitre()] = $jeu;
        }

        // [prénom, nom, pseudo, mail, date de naissance, collection]
        // collection : [titre du jeu, statut, prix d'achat, date d'achat, commentaire]
        $utilisateursData = [
            ['Denis', 'Dupont', 'DenisDu', 'denis.dupont@exemple.fr', '1985-03-14', [
                ['Elden Ring', StatutJeuEnum::EN_COURS, '49.99', '2022-03-02', 'Bloqué sur Malenia depuis deux semaines.'],
                ['The Witcher 3: Wild Hunt', StatutJeuEnum::PLATINE, '19.99', '2016-01-10', 'Mon jeu préféré, toutes les quêtes finies.'],
                ['Cyberpunk 2077', StatutJeuEnum::TERMINE, '29.99', '2021-06-20', null],
                ['Grand Theft Auto V', StatutJeuEnum::VENDU, '15.00', '2014-02-08', 'Revendu après l\'avoir fini.'],
                ['Death Stranding', StatutJeuEnum::ABANDONNE, '24.99', '2020-07-15', 'Trop lent pour moi.'],
                ['Sid Meier\'s Civilization VI', StatutJeuEnum::POSSEDE, '9.99', '2019-11-29', null],
                ['Final Fantasy XVI', StatutJeuEnum::SOUHAITE, null, null, 'À acheter pendant les soldes.'],
            ]],
            ['Marcelle', 'Dumont', 'Marcelle_D', 'marcelle.dumont@exemple.fr', '1992-07-01', [
                ['Animal Crossing: New Horizons', StatutJeuEnum::PLATINE, '54.99', '2020-03-20', 'Mon île a 5 étoiles !'],
                ['Mario Kart 8 Deluxe', StatutJeuEnum::POSSEDE, '44.99', '2018-12-24', 'Cadeau de Noël.'],
                ['Super Mario Bros. Wonder', StatutJeuEnum::TERMINE, '49.99', '2023-10-20', null],
                ['The Legend of Zelda: Tears of the Kingdom', StatutJeuEnum::EN_COURS, '59.99', '2023-05-12', null],
                ['Les Sims 4', StatutJeuEnum::PRETE, null, '2019-08-03', 'Prêté à ma sœur.'],
                ['Captain Toad: Treasure Tracker', StatutJeuEnum::TERMINE, '29.99', '2018-07-13', null],
            ]],
            ['Inès', 'Benali', 'inesb', 'ines.benali@exemple.fr', '2001-11-23', [
                ['Persona 5 Royal', StatutJeuEnum::PLATINE, '39.99', '2020-04-02', '120 heures, aucun regret.'],
                ['Life is Strange', StatutJeuEnum::TERMINE, '9.99', '2017-02-14', null],
                ['Detroit: Become Human', StatutJeuEnum::TERMINE, '19.99', '2019-05-25', 'Fin « tout le monde survit » obtenue.'],
                ['Portal 2', StatutJeuEnum::POSSEDE, '4.99', '2020-06-30', null],
                ['The Talos Principle 2', StatutJeuEnum::EN_COURS, '29.99', '2023-11-10', null],
                ['A Plague Tale: Requiem', StatutJeuEnum::TERMINE, '39.99', '2023-01-05', null],
                ['Astro Bot', StatutJeuEnum::SOUHAITE, null, null, null],
                ['Marvel\'s Spider-Man 2', StatutJeuEnum::POSSEDE, '69.99', '2023-10-20', null],
            ]],
            ['Lucas', 'Moreau', 'LucasM', 'lucas.moreau@exemple.fr', '1998-02-05', [
                ['EA Sports FC 25', StatutJeuEnum::EN_COURS, '69.99', '2024-09-27', 'Mode carrière avec le PSG.'],
                ['NBA 2K25', StatutJeuEnum::POSSEDE, '59.99', '2024-10-12', null],
                ['Gran Turismo 7', StatutJeuEnum::TERMINE, '39.99', '2022-08-01', null],
                ['Forza Horizon 5', StatutJeuEnum::PLATINE, '34.99', '2022-01-15', null],
                ['Street Fighter 6', StatutJeuEnum::ABANDONNE, '29.99', '2023-12-01', null],
                ['Mario Tennis Aces', StatutJeuEnum::VENDU, '20.00', '2018-07-01', null],
            ]],
            ['Chloé', 'Martin', 'chloe.m', 'chloe.martin@exemple.fr', '2004-09-18', [
                ['Resident Evil 4', StatutJeuEnum::EN_COURS, '39.99', '2024-10-31', 'Pour Halloween.'],
                ['Microsoft Flight Simulator', StatutJeuEnum::SOUHAITE, null, null, null],
                ['Anno 1800', StatutJeuEnum::POSSEDE, '14.99', '2023-03-18', null],
            ]],
            // Aucun jeu dans sa collection (cas demandé par le sujet)
            ['Hugo', 'Lefèvre', 'hugo_lf', 'hugo.lefevre@exemple.fr', '1979-05-30', []],
        ];

        foreach ($utilisateursData as [$prenom, $nom, $pseudo, $mail, $naissance, $collection]) {
            $utilisateur = (new Utilisateur())
                ->setPrenom($prenom)
                ->setNom($nom)
                ->setPseudonyme($pseudo)
                ->setMail($mail)
                ->setDateNaissance(new \DateTime($naissance));
            $manager->persist($utilisateur);

            foreach ($collection as [$titre, $statut, $prixAchat, $dateAchat, $commentaire]) {
                if (!isset($jeux[$titre])) {
                    continue; // jeu absent de la BDD : on l'ignore
                }

                $collect = (new Collect())
                    ->setJeuVideo($jeux[$titre])
                    ->setStatut($statut)
                    ->setPrixAchat($prixAchat)
                    ->setDateAchat($dateAchat ? new \DateTime($dateAchat) : null)
                    ->setCommentaire($commentaire);
                // Date de changement de statut : le jour de l'achat (ou maintenant pour un souhait)
                $collect->setDateModifStatut($dateAchat ? new \DateTimeImmutable($dateAchat.' 18:00') : new \DateTimeImmutable());

                $utilisateur->addCollect($collect);
                $manager->persist($collect);
            }
        }

        $manager->flush();
    }
}
