<?php

namespace App\Domain\Roulette\Enums;

/**
 * Types de mises supportés à la roulette américaine (plein, chances simples,
 * douzaines, colonnes). Chaque type porte son propre multiplicateur de gain.
 */
enum RouletteBetType: string
{
    case STRAIGHT = 'straight';
    case RED = 'red';
    case BLACK = 'black';
    case EVEN = 'even';
    case ODD = 'odd';
    case LOW = 'low';
    case HIGH = 'high';
    case DOZEN_1 = 'dozen_1';
    case DOZEN_2 = 'dozen_2';
    case DOZEN_3 = 'dozen_3';
    case COLUMN_1 = 'column_1';
    case COLUMN_2 = 'column_2';
    case COLUMN_3 = 'column_3';

    public function label(): string
    {
        return match ($this) {
            self::STRAIGHT => 'Plein (numéro exact)',
            self::RED => 'Rouge',
            self::BLACK => 'Noir',
            self::EVEN => 'Pair',
            self::ODD => 'Impair',
            self::LOW => 'Manque (1-18)',
            self::HIGH => 'Passe (19-36)',
            self::DOZEN_1 => '1ère douzaine (1-12)',
            self::DOZEN_2 => '2e douzaine (13-24)',
            self::DOZEN_3 => '3e douzaine (25-36)',
            self::COLUMN_1 => '1ère colonne',
            self::COLUMN_2 => '2e colonne',
            self::COLUMN_3 => '3e colonne',
        };
    }

    /**
     * Multiplicateur de gain net (hors remise de la mise) si le pari est gagnant.
     */
    public function payoutMultiplier(): int
    {
        return match ($this) {
            self::STRAIGHT => 35,
            self::RED, self::BLACK, self::EVEN, self::ODD, self::LOW, self::HIGH => 1,
            self::DOZEN_1, self::DOZEN_2, self::DOZEN_3, self::COLUMN_1, self::COLUMN_2, self::COLUMN_3 => 2,
        };
    }

    public function requiresNumber(): bool
    {
        return $this === self::STRAIGHT;
    }

    /**
     * @return array<int, self> Toutes les mises "chances simples" (gain x1).
     */
    public static function simpleChances(): array
    {
        return [self::RED, self::BLACK, self::EVEN, self::ODD, self::LOW, self::HIGH];
    }

    /**
     * @return array<int, self>
     */
    public static function dozens(): array
    {
        return [self::DOZEN_1, self::DOZEN_2, self::DOZEN_3];
    }

    /**
     * @return array<int, self>
     */
    public static function columns(): array
    {
        return [self::COLUMN_1, self::COLUMN_2, self::COLUMN_3];
    }
}
