<?php

namespace App\Domain\Draw\Enums;

/**
 * Backed Enum (Enum typé) définissant les modes d'affichage visuel du tirage.
 * Permet de découpler la logique de rendu (UI) de l'algorithme métier de sélection.
 */
enum DrawDisplay: string
{
    case SIMPLE = 'simple';
    case WHEEL = 'wheel';

    /**
     * Traduit la valeur technique de l'Enum en libellé lisible pour l'interface utilisateur.
     * S'appuie sur un "match" sur l'instance ($this) pour un mappage strict et sécurisé.
     */
    public function label(): string
    {
        return match ($this) {
            self::SIMPLE => 'Simple',
            self::WHEEL => 'Roue',
        };
    }
}
