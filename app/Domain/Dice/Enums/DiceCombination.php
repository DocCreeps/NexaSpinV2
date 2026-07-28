<?php

namespace App\Domain\Dice\Enums;

/**
 * Combinaisons possibles pour un lancer de 3 dés.
 */
enum DiceCombination: string
{
    case FOUR_TWO_ONE = 'four_two_one';
    case BRELAN = 'brelan';
    case SUITE = 'suite';
    case NONE = 'none';

    /**
     * Libellé lisible de la combinaison.
     */
    public function label(): string
    {
        return match ($this) {
            self::FOUR_TWO_ONE => '421',
            self::BRELAN => 'Brelan',
            self::SUITE => 'Suite',
            self::NONE => 'Aucune combinaison',
        };
    }
}
