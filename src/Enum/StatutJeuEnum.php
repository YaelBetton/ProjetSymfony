<?php

namespace App\Enum;

/**
 * Statut d'un jeu dans la collection d'un utilisateur (état à une date donnée).
 *
 * Les valeurs sont portées par cette énumération : aucune table n'est nécessaire en BDD,
 * la colonne collect.statut contient directement la valeur (ex. « EN_COURS »).
 */
enum StatutJeuEnum: string
{
    case POSSEDE = 'POSSEDE';
    case SOUHAITE = 'SOUHAITE';
    case EN_COURS = 'EN_COURS';
    case TERMINE = 'TERMINE';
    case ABANDONNE = 'ABANDONNE';
    case PRETE = 'PRETE';
    case VENDU = 'VENDU';
    case PLATINE = 'PLATINE';

    /**
     * Libellé « human-readable » du statut (affiché dans les vues et les formulaires).
     */
    public function getLabel(): string
    {
        return match ($this) {
            self::POSSEDE => 'Possédé',
            self::SOUHAITE => 'Souhaité',
            self::EN_COURS => 'En cours',
            self::TERMINE => 'Terminé',
            self::ABANDONNE => 'Abandonné',
            self::PRETE => 'Prêté',
            self::VENDU => 'Vendu',
            self::PLATINE => 'Platine',
        };
    }

    /**
     * Modificateur CSS du badge de statut (cf. public/css/collection.css).
     * Le libellé reste toujours affiché : l'information ne repose pas que sur la couleur.
     */
    public function getBadgeClass(): string
    {
        return 'badge--statut-'.strtolower(str_replace('_', '-', $this->value));
    }
}
