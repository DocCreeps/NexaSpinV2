<?php

namespace App\Domain\Dice\Strategies;

use App\Domain\Dice\Contracts\DiceGameStrategy;
use App\Domain\Dice\Enums\DiceCombination;
use App\Domain\Dice\Support\DiceCombinationEvaluator;
use App\Domain\Dice\ValueObjects\DiceRoll;

/**
 * Implémentation de la stratégie des règles classiques du 421.
 */
final class FourTwoOneStrategy implements DiceGameStrategy
{
    private const DICE_COUNT = 3;
    private const MAX_THROWS = 3;

    public function diceCount(): int
    {
        return self::DICE_COUNT;
    }

    public function maxThrows(): int
    {
        return self::MAX_THROWS;
    }

    public function isWinningRoll(DiceRoll $roll): bool
    {
        return DiceCombinationEvaluator::detect($roll) === DiceCombination::FOUR_TWO_ONE;
    }
}
