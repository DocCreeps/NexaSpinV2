<?php

namespace App\Domain\Dice\Enums;

/**
 * Combinaisons détectables sur un lancer de 3 dés.
 *
 * Ce vocabulaire est commun à plusieurs jeux de dés français (421,
 * Zanzibar...), pas uniquement au 421 : c'est pour ça qu'il vit à côté du
 * contrat DiceGameStrategy plutôt que dans FourTwoOneStrategy. Chaque
 * stratégie reste libre de n'utiliser qu'une partie de ces cases comme
 * objectif de victoire.
 */
enum DiceCombination: string
{
    case FOUR_TWO_ONE = 'four_two_one';
    case BRELAN = 'brelan';
    case SUITE = 'suite';
    case NONE = 'none';

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
