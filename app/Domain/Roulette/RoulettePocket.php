<?php

namespace App\Domain\Roulette;

/**
 * Table des 38 cases de la roulette américaine (0, 00, 1 à 36) et de leurs
 * propriétés (couleur, parité, douzaine, colonne), utilisées pour évaluer les
 * mises de type "chances simples" et "combinaisons".
 */
final class RoulettePocket
{
    private const RED_NUMBERS = [1, 3, 5, 7, 9, 12, 14, 16, 18, 19, 21, 23, 25, 27, 30, 32, 34, 36];

    /**
     * @return array<int, string> Les 38 cases, dans l'ordre physique de la roue.
     */
    public static function all(): array
    {
        return ['0', '00', ...array_map('strval', range(1, 36))];
    }

    public static function random(): string
    {
        $pockets = self::all();

        return $pockets[random_int(0, count($pockets) - 1)];
    }

    public static function isZero(string $pocket): bool
    {
        return $pocket === '0' || $pocket === '00';
    }

    public static function color(string $pocket): string
    {
        if (self::isZero($pocket)) {
            return 'green';
        }

        return in_array((int) $pocket, self::RED_NUMBERS, true) ? 'red' : 'black';
    }

    public static function isEven(string $pocket): bool
    {
        return ! self::isZero($pocket) && ((int) $pocket) % 2 === 0;
    }

    /**
     * Numéro de douzaine (1, 2 ou 3), ou null pour 0/00 (hors douzaines).
     */
    public static function dozen(string $pocket): ?int
    {
        if (self::isZero($pocket)) {
            return null;
        }

        return (int) ceil(((int) $pocket) / 12);
    }

    /**
     * Numéro de colonne (1, 2 ou 3), ou null pour 0/00 (hors colonnes).
     */
    public static function column(string $pocket): ?int
    {
        if (self::isZero($pocket)) {
            return null;
        }

        $mod = ((int) $pocket) % 3;

        return $mod === 0 ? 3 : $mod;
    }
}
