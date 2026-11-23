<?php

namespace App\DataFixtures;

use App\Entity\Developpeur;
use App\Entity\Editeur;
use App\Entity\Genre;
use App\Entity\JeuVideo;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Données de test : genres, éditeurs, développeurs et jeux vidéo.
 * Chargement : php bin/console doctrine:fixtures:load (purge la BDD avant).
 */
class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // ----- Genres (les 9 genres attendus par le sujet) -----
        $genresData = [
            'action'          => ['ACTION', "Jeux d'action : jeux de plateforme, combat, tir (FPS, TPS, …)"],
            'aventure'        => ['AVENTURE', "Jeux d'aventure narrative, point and click…"],
            'action_aventure' => ['ACTION_AVENTURE', "Jeux d'action-aventure (infiltration, survival, …)"],
            'rpg'             => ['RPG', 'Jeux de rôle, MMORPG, …'],
            'strategie'       => ['STRATEGIE', 'Jeux de stratégie (RTS, turn-based)'],
            'simulation'      => ['SIMULATION', 'Jeux de simulation, de gestion'],
            'sport'           => ['SPORT', 'Jeux de sport'],
            'course'          => ['COURSE', 'Jeux de course, par ex. automobile'],
            'reflexion'       => ['REFLEXION', 'Jeux de réflexion, puzzles, casse-tête'],
        ];

        $genres = [];
        foreach ($genresData as $key => [$nom, $description]) {
            $genre = new Genre();
            $genre->setNom($nom);
            $genre->setDescription($description);
            $genre->setActif(true);
            $manager->persist($genre);
            $genres[$key] = $genre;
        }

        // Un genre inactif (TP4) : il ne doit pas être proposé dans le formulaire d'un jeu vidéo
        $genreInactif = new Genre();
        $genreInactif->setNom('MMO');
        $genreInactif->setDescription('Jeux massivement multijoueurs en ligne (genre désactivé)');
        $genreInactif->setActif(false);
        $manager->persist($genreInactif);

        // ----- Éditeurs : [nom, pays, site web, description] -----
        $editeursData = [
            'sony'      => ['Sony Interactive Entertainment', 'Japon', 'https://www.sie.com', 'Éditeur des consoles PlayStation et de ses exclusivités.'],
            'nintendo'  => ['Nintendo', 'Japon', 'https://www.nintendo.com', 'Constructeur et éditeur japonais historique (Mario, Zelda, Pokémon…).'],
            'ubisoft'   => ['Ubisoft', 'France', 'https://www.ubisoft.com', "Éditeur français fondé en 1986 (Assassin's Creed, Anno…)."],
            'ea'        => ['Electronic Arts', 'États-Unis', 'https://www.ea.com', 'Éditeur américain, spécialiste des jeux de sport (EA Sports).'],
            'cdprojekt' => ['CD Projekt', 'Pologne', 'https://www.cdprojekt.com', 'Éditeur polonais de The Witcher et Cyberpunk 2077.'],
            'microsoft' => ['Xbox Game Studios', 'États-Unis', 'https://www.xbox.com', 'Branche édition de Microsoft pour les jeux Xbox et PC.'],
            'square'    => ['Square Enix', 'Japon', 'https://www.square-enix.com', 'Éditeur japonais de Final Fantasy et Dragon Quest.'],
            'capcom'    => ['Capcom', 'Japon', 'https://www.capcom.com', 'Éditeur japonais de Resident Evil et Street Fighter.'],
            'bandai'    => ['Bandai Namco Entertainment', 'Japon', 'https://www.bandainamcoent.com', 'Éditeur japonais (Elden Ring, Tekken, Pac-Man…).'],
            'sega'      => ['Sega', 'Japon', 'https://www.sega.com', 'Éditeur japonais (Sonic, Total War, Persona…).'],
            'rockstar'  => ['Rockstar Games', 'États-Unis', 'https://www.rockstargames.com', 'Éditeur de la série Grand Theft Auto.'],
            'focus'     => ['Focus Entertainment', 'France', 'https://www.focus-entmt.com', 'Éditeur français (A Plague Tale, Space Marine 2…).'],
            '2k'        => ['2K', 'États-Unis', 'https://www.2k.com', 'Éditeur américain (Civilization, NBA 2K…).'],
            'valve'     => ['Valve', 'États-Unis', 'https://www.valvesoftware.com', 'Éditeur de Half-Life et Portal, créateur de Steam.'],
            'devolver'  => ['Devolver Digital', 'États-Unis', 'https://www.devolverdigital.com', 'Éditeur de jeux indépendants.'],
        ];

        $editeurs = [];
        foreach ($editeursData as $key => [$nom, $pays, $site, $description]) {
            $editeur = new Editeur();
            $editeur->setNom($nom);
            $editeur->setPays($pays);
            $editeur->setSiteWeb($site);
            $editeur->setDescription($description);
            $manager->persist($editeur);
            $editeurs[$key] = $editeur;
        }

        // ----- Développeurs : [nom, pays, site web] -----
        $developpeursData = [
            'naughtydog'     => ['Naughty Dog', 'États-Unis', 'https://www.naughtydog.com'],
            'asobi'          => ['Team Asobi', 'Japon', null],
            'nintendoepd'    => ['Nintendo EPD', 'Japon', 'https://www.nintendo.com'],
            'capcom'         => ['Capcom', 'Japon', 'https://www.capcom.com'],
            'quantic'        => ['Quantic Dream', 'France', 'https://www.quanticdream.com'],
            'dontnod'        => ["Don't Nod", 'France', null],
            'santamonica'    => ['Santa Monica Studio', 'États-Unis', null],
            'insomniac'      => ['Insomniac Games', 'États-Unis', null],
            'asobo'          => ['Asobo Studio', 'France', 'https://www.asobostudio.com'],
            'kojima'         => ['Kojima Productions', 'Japon', null],
            'ubibordeaux'    => ['Ubisoft Bordeaux', 'France', null],
            'rockstarnorth'  => ['Rockstar North', 'Royaume-Uni', null],
            'cdpred'         => ['CD Projekt Red', 'Pologne', null],
            'fromsoftware'   => ['FromSoftware', 'Japon', 'https://www.fromsoftware.jp'],
            'square'         => ['Square Enix', 'Japon', 'https://www.square-enix.com'],
            'atlus'          => ['Atlus', 'Japon', null],
            'ubimainz'       => ['Ubisoft Mainz', 'Allemagne', null],
            'firaxis'        => ['Firaxis Games', 'États-Unis', 'https://www.firaxis.com'],
            'ca'             => ['Creative Assembly', 'Royaume-Uni', 'https://www.creative-assembly.com'],
            'maxis'          => ['Maxis', 'États-Unis', null],
            'eavancouver'    => ['EA Vancouver', 'Canada', null],
            'visualconcepts' => ['Visual Concepts', 'États-Unis', null],
            'camelot'        => ['Camelot Software Planning', 'Japon', null],
            'polyphony'      => ['Polyphony Digital', 'Japon', null],
            'playground'     => ['Playground Games', 'Royaume-Uni', null],
            'valve'          => ['Valve', 'États-Unis', 'https://www.valvesoftware.com'],
            'croteam'        => ['Croteam', 'Croatie', null],
        ];

        $developpeurs = [];
        foreach ($developpeursData as $key => [$nom, $pays, $site]) {
            $developpeur = new Developpeur();
            $developpeur->setNom($nom);
            $developpeur->setPays($pays);
            $developpeur->setSiteWeb($site);
            $manager->persist($developpeur);
            $developpeurs[$key] = $developpeur;
        }

        // ----- Jeux vidéo : [titre, développeur, éditeur, genre, date de sortie, prix, description] -----
        $jeuxData = [
            // ACTION
            ['The Last of Us Part II', 'naughtydog', 'sony', 'action', '2020-06-19', '59.99', 'Ellie traverse une Amérique post-apocalyptique en quête de vengeance.'],
            ['Astro Bot', 'asobi', 'sony', 'action', '2024-09-06', '79.99', 'Jeu de plateforme où le petit robot Astro part sauver son équipage.'],
            ['Super Mario Bros. Wonder', 'nintendoepd', 'nintendo', 'action', '2023-10-20', '59.99', 'Plateforme 2D où les fleurs prodiges transforment les niveaux.'],
            ['Street Fighter 6', 'capcom', 'capcom', 'action', '2023-06-02', '59.99', 'Jeu de combat en un contre un.'],
            // AVENTURE
            ['The Legend of Zelda: Tears of the Kingdom', 'nintendoepd', 'nintendo', 'aventure', '2023-05-12', '69.99', 'Link explore Hyrule, ses îles célestes et ses profondeurs.'],
            ['Detroit: Become Human', 'quantic', 'sony', 'aventure', '2018-05-25', '19.99', "Aventure narrative où l'on suit trois androïdes à Detroit."],
            ['Life is Strange', 'dontnod', 'square', 'aventure', '2015-01-30', '19.99', "Max découvre qu'elle peut remonter le temps."],
            // ACTION_AVENTURE
            ['God of War Ragnarök', 'santamonica', 'sony', 'action_aventure', '2022-11-09', '69.99', 'Kratos et Atreus face au Ragnarök dans la mythologie nordique.'],
            ["Marvel's Spider-Man 2", 'insomniac', 'sony', 'action_aventure', '2023-10-20', '79.99', 'Peter Parker et Miles Morales protègent New York.'],
            ['A Plague Tale: Requiem', 'asobo', 'focus', 'action_aventure', '2022-10-18', '59.99', "Amicia et Hugo fuient la peste et l'Inquisition (infiltration)."],
            ['Death Stranding', 'kojima', 'sony', 'action_aventure', '2019-11-08', '69.99', 'Sam Porter Bridges reconnecte une Amérique dévastée.'],
            ["Assassin's Creed Mirage", 'ubibordeaux', 'ubisoft', 'action_aventure', '2023-10-05', '49.99', 'Basim devient assassin dans la Bagdad du IXe siècle.'],
            ['Resident Evil 4', 'capcom', 'capcom', 'action_aventure', '2023-03-24', '59.99', 'Remake du survival horror : Leon S. Kennedy en Espagne.'],
            ['Grand Theft Auto V', 'rockstarnorth', 'rockstar', 'action_aventure', '2013-09-17', '29.99', 'Trois criminels dans la ville de Los Santos.'],
            // RPG
            ['The Witcher 3: Wild Hunt', 'cdpred', 'cdprojekt', 'rpg', '2015-05-19', '29.99', 'Geralt de Riv part à la recherche de Ciri.'],
            ['Cyberpunk 2077', 'cdpred', 'cdprojekt', 'rpg', '2020-12-10', '39.99', 'RPG en monde ouvert dans la mégalopole de Night City.'],
            ['Elden Ring', 'fromsoftware', 'bandai', 'rpg', '2022-02-25', '59.99', "Action-RPG exigeant dans l'Entre-terre."],
            ['Final Fantasy XVI', 'square', 'square', 'rpg', '2023-06-22', '79.99', 'Clive Rosfield dans le monde de Valisthéa.'],
            ['Persona 5 Royal', 'atlus', 'sega', 'rpg', '2020-03-31', '59.99', 'Des lycéens voleurs de cœurs à Tokyo.'],
            // STRATEGIE
            ['Anno 1800', 'ubimainz', 'ubisoft', 'strategie', '2019-04-16', '59.99', 'Construction de villes et commerce pendant la révolution industrielle.'],
            ["Sid Meier's Civilization VI", 'firaxis', '2k', 'strategie', '2016-10-21', '59.99', 'Stratégie au tour par tour : bâtir une civilisation à travers les âges.'],
            ['Total War: Warhammer III', 'ca', 'sega', 'strategie', '2022-02-17', '59.99', "Stratégie en temps réel et au tour par tour dans l'univers Warhammer."],
            // SIMULATION
            ['Microsoft Flight Simulator', 'asobo', 'microsoft', 'simulation', '2020-08-18', '69.99', 'Simulation de vol sur la planète entière.'],
            ['Animal Crossing: New Horizons', 'nintendoepd', 'nintendo', 'simulation', '2020-03-20', '59.99', 'Simulation de vie sur une île déserte à aménager.'],
            ['Les Sims 4', 'maxis', 'ea', 'simulation', '2014-09-04', '39.99', 'Simulation de vie : créer des Sims et gérer leur quotidien.'],
            // SPORT
            ['EA Sports FC 25', 'eavancouver', 'ea', 'sport', '2024-09-27', '79.99', 'Simulation de football.'],
            ['NBA 2K25', 'visualconcepts', '2k', 'sport', '2024-09-06', '69.99', 'Simulation de basket-ball NBA.'],
            ['Mario Tennis Aces', 'camelot', 'nintendo', 'sport', '2018-06-22', '59.99', "Tennis arcade avec les personnages de l'univers Mario."],
            // COURSE
            ['Gran Turismo 7', 'polyphony', 'sony', 'course', '2022-03-04', '69.99', 'Simulation de course automobile.'],
            ['Mario Kart 8 Deluxe', 'nintendoepd', 'nintendo', 'course', '2017-04-28', '59.99', 'Course de karts avec Mario et ses amis.'],
            ['Forza Horizon 5', 'playground', 'microsoft', 'course', '2021-11-09', '69.99', 'Course en monde ouvert au Mexique.'],
            // REFLEXION
            ['Portal 2', 'valve', 'valve', 'reflexion', '2011-04-19', '9.99', 'Énigmes à résoudre avec un pistolet à portails.'],
            ['Captain Toad: Treasure Tracker', 'nintendoepd', 'nintendo', 'reflexion', '2015-01-02', '39.99', 'Casse-têtes en dioramas avec Captain Toad.'],
            ['The Talos Principle 2', 'croteam', 'devolver', 'reflexion', '2023-11-02', '29.99', 'Puzzles à la première personne et réflexion philosophique.'],
        ];

        foreach ($jeuxData as [$titre, $devKey, $editeurKey, $genreKey, $date, $prix, $description]) {
            $jeu = new JeuVideo();
            $jeu->setTitre($titre);
            $jeu->setDeveloppeur($developpeurs[$devKey]);
            $jeu->setEditeur($editeurs[$editeurKey]);
            $jeu->setGenre($genres[$genreKey]);
            $jeu->setDateSortie(new \DateTime($date));
            $jeu->setPrix($prix);
            $jeu->setDescription($description);
            $manager->persist($jeu);
        }

        $manager->flush();
    }
}
