<?php

namespace App\Domain\Dice\ValueObjects;

use App\Domain\Dice\Enums\DiceCombination;

/**
 * Résultat d'un lancer au sein d'une partie : le lancer lui-même, la
 * combinaison détectée, le nombre de lancers déjà effectués, et si la
 * partie est gagnée / terminée.
 */
final readonly class DiceThrowResult
{
    public function __construct(
        public DiceRoll $roll,
        public DiceCombination $combination,
        public int $throwCount,
        public bool $isWon,
        public bool $isOver,
    ) {}
}
