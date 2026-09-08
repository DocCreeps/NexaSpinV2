<?php

namespace App\Domain\DiceRoller\Enums;

/**
 * Types de dés réellement existants dans un set de jeu de rôle (polyédriques
 * standards). Volontairement fermé : pas de "d7" ni de valeur arbitraire —
 * seuls les types physiquement vendus sont proposés.
 */
enum DiceFaceCount: int
{
    case D4 = 4;
    case D6 = 6;
    case D8 = 8;
    case D10 = 10;
    case D12 = 12;
    case D20 = 20;
    case D100 = 100;

    public function label(): string
    {
        return 'd'.$this->value;
    }
}
