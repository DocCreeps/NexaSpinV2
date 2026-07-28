<?php

namespace App\Domain\Dice\ValueObjects;

use App\Domain\Dice\Enums\DiceCombination;

/**
 * Représente le résultat et l'état de la partie après un lancer de dés.
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
