<?php

namespace App\Domain\Draw\Enums;

/**
 * Backed Enum (Enum typé) listant les types de tirage au sort disponibles.
 * Centralise les stratégies algorithmiques supportées par le système.
 */
enum DrawType: string
{
    case RANDOM = 'random';
    case WEIGHTED = 'weighted';

    /**
     * Retourne le libellé utilisateur correspondant au type de tirage.
     * Utilise "match" sur l'état courant ($this) pour garantir l'exhaustivité des cas.
     */
    public function label(): string
    {
        return match ($this) {
            self::RANDOM => 'Aléatoire',
            self::WEIGHTED => 'Pondéré',
        };
    }
}
