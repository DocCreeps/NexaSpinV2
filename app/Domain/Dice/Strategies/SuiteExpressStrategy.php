<?php

namespace App\Domain\Dice\Strategies;

use App\Domain\Dice\Contracts\DiceGameStrategy;
use App\Domain\Dice\Enums\DiceCombination;
use App\Domain\Dice\Support\DiceCombinationEvaluator;
use App\Domain\Dice\ValueObjects\DiceRoll;

/**
 * Variante rapide : viser une Suite (3 valeurs consécutives) en 2 lancers
 * maximum au lieu du 421 classique en 3 lancers. Réutilise entièrement
 * DiceCombinationEvaluator (aucune modification de RollDiceAction requise).
 */
final class SuiteExpressStrategy implements DiceGameStrategy
{
    private const DICE_COUNT = 3;

    private const MAX_THROWS = 2;

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
        return DiceCombinationEvaluator::detect($roll) === DiceCombination::SUITE;
    }
}
