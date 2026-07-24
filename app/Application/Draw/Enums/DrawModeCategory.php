<?php

namespace App\Application\Draw\Enums;

/**
 * Catégorie de présentation d'un mode de tirage sur la page d'accueil.
 *
 * Distinct de Domain\Draw\Enums\DrawDisplay (qui pilote le rendu du tirage
 * lui-même, roue vs affichage simple) : ceci ne sert qu'à regrouper les
 * cartes de la home. L'ordre des cases définit l'ordre d'affichage des
 * sections ; ajouter une nouvelle catégorie ici suffit à faire apparaître
 * une nouvelle section sur la home, sans toucher au Controller ni à la vue.
 */
enum DrawModeCategory: string
{
    case WHEEL = 'wheel';
    case OTHER = 'other';
    case DEV = 'dev';

    public function label(): string
    {
        return match ($this) {
            self::WHEEL => 'Roues',
            self::OTHER => 'Autres tirages',
            self::DEV => 'En Développement',
        };
    }
}
