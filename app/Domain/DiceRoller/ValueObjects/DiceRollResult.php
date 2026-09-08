<?php

namespace App\Domain\DiceRoller\ValueObjects;

use App\Domain\DiceRoller\Enums\DiceFaceCount;

/**
 * Résultat immuable d'un lancer d'un ou plusieurs dés identiques (ex: 3d6,
 * 1d20). Le type de dé est verrouillé via DiceFaceCount (enum fermé) : cette
 * classe n'a donc pas besoin de revalider elle-même les bornes de chaque
 * valeur, contrairement à un Value Object qui prendrait un entier brut.
 */
final readonly class DiceRollResult
{
    /**
     * @param array<int, int> $values Une valeur par dé lancé, dans l'ordre.
     */
    public function __construct(
        public DiceFaceCount $faces,
        public array $values,
    ) {}

    public function sum(): int
    {
        return array_sum($this->values);
    }

    public function count(): int
    {
        return count($this->values);
    }
}
