<?php

namespace App\Application\Home\Enums;

/**
 * Catégorie de présentation d'un mode de jeu sur la page d'accueil.
 *
 */
enum GameModeCategory: string
{
    case WHEEL = 'wheel';
    case GAME = 'games';
    case TOOLS = 'tools';
    case OTHER = 'other';
    case DEV = 'dev';


    public function label(): string
    {
        return match ($this) {
            self::WHEEL => 'Roues',
            self::OTHER => 'Autres tirages',
            self::DEV => 'En Développement',
            self::GAME => 'Jeux',
            self::TOOLS => 'Outils',
        };
    }
}
