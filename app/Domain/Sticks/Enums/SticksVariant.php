<?php

namespace App\Domain\Sticks\Enums;

/**
 * Règle de fin du jeu des bâtonnets.
 */
enum SticksVariant: string
{
    /** Variante classique : celui qui récupère le dernier bâtonnet perd. */
    case LAST_LOSES = 'last_loses';

    /** Variante inversée : celui qui récupère le dernier bâtonnet gagne. */
    case LAST_WINS = 'last_wins';

    public function label(): string
    {
        return match ($this) {
            self::LAST_LOSES => 'Le dernier perd',
            self::LAST_WINS => 'Le dernier gagne',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::LAST_LOSES => 'Celui qui récupère le dernier bâtonnet perd !',
            self::LAST_WINS => 'Celui qui récupère le dernier bâtonnet gagne !',
        };
    }
}
