<?php

namespace App\Domain\Dice\Support;

use App\Domain\Dice\Enums\DiceCombination;
use App\Domain\Dice\ValueObjects\DiceRoll;

/**
 * Évalue la combinaison formée par un lancer de dés.
 */
final class DiceCombinationEvaluator
{
    public static function detect(DiceRoll $roll): DiceCombination
    {
        $sorted = $roll->sorted();

        if ($sorted === [1, 2, 4]) {
            return DiceCombination::FOUR_TWO_ONE;
        }

        if (count(array_unique($sorted)) === 1) {
            return DiceCombination::BRELAN;
        }

        if (self::isConsecutiveSuite($sorted)) {
            return DiceCombination::SUITE;
        }

        return DiceCombination::NONE;
    }

    /**
     * @param array<int> $sorted
     */
    private static function isConsecutiveSuite(array $sorted): bool
    {
        return $sorted[1] === $sorted[0] + 1
            && $sorted[2] === $sorted[1] + 1;
    }
}
