<?php

namespace App\Application\DiceRoller\Actions;

use App\Domain\DiceRoller\Enums\DiceFaceCount;
use App\Domain\DiceRoller\Exceptions\InvalidDiceRollException;
use App\Domain\DiceRoller\ValueObjects\DiceRollResult;

/**
 * Lance N dés identiques (même type) et retourne le résultat. Utilise
 * random_int() (CSPRNG), comme tous les autres tirages de l'application
 * (Participants::random(), RandomCoinFlipStrategy, RoulettePocket::random()...).
 */
final class RollDiceSetAction
{
    private const MIN_DICE = 1;
    private const MAX_DICE = 20;

    public function execute(DiceFaceCount $faces, int $diceCount): DiceRollResult
    {
        if ($diceCount < self::MIN_DICE || $diceCount > self::MAX_DICE) {
            throw new InvalidDiceRollException(
                sprintf('Le nombre de dés doit être compris entre %d et %d.', self::MIN_DICE, self::MAX_DICE)
            );
        }

        $values = [];

        for ($i = 0; $i < $diceCount; $i++) {
            $values[] = random_int(1, $faces->value);
        }

        return new DiceRollResult($faces, $values);
    }
}
