<?php

namespace App\Domain\Dice\Strategies;

use App\Domain\Dice\Contracts\DiceGameStrategy;
use App\Domain\Dice\Enums\DiceCombination;
use App\Domain\Dice\Support\DiceCombinationEvaluator;
use App\Domain\Dice\ValueObjects\DiceRoll;

/**
 * Règles du 421 classique : 3 dés, 3 lancers maximum, objectif = combinaison
 * 4-2-1.
 *
 * C'est la SEULE classe du module Dice à connaître le nombre "3" ou la
 * combinaison FOUR_TWO_ONE. Un futur jeu de dés (Zanzibar, brelan simple,
 * un mode où l'objectif est une suite...) s'ajoute en implémentant
 * DiceGameStrategy dans une nouvelle classe, sans toucher à celle-ci ni au
 * contrat.
 */
final class FourTwoOneStrategy implements DiceGameStrategy
{
    private const DICE_COUNT = 3;
    private const MAX_THROWS = 5;

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
