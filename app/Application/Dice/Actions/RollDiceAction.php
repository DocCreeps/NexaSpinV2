<?php

namespace App\Application\Dice\Actions;

use App\Domain\Dice\Contracts\DiceGameStrategy;
use App\Domain\Dice\Support\DiceCombinationEvaluator;
use App\Domain\Dice\ValueObjects\DiceRoll;
use App\Domain\Dice\ValueObjects\DiceThrowResult;

/**
 * Action (Use Case) orchestrant un lancer de dés.
 * Classe fermée à l'extension (final) possédant une unique responsabilité.
 *
 * Ne connaît ni "421", ni le nombre de dés, ni le nombre de lancers
 * autorisés : elle délègue entièrement ces règles à la DiceGameStrategy
 * injectée. Relancer un jeu de dés différent (via un binding contextuel du
 * conteneur) ne nécessite aucune modification de cette classe.
 */
final class RollDiceAction
{
    public function __construct(
        private readonly DiceGameStrategy $strategy
    ) {}

    /**
     * @param array<int> $currentValues Valeurs actuelles des dés (avant relance)
     * @param array<bool> $kept Dés à conserver (même index que $currentValues) ; un dé
     *                          absent ou marqué false est relancé via random_int (CSPRNG)
     */
    public function execute(array $currentValues, array $kept, int $throwCount): DiceThrowResult
    {
        $values = [];

        foreach (array_values($currentValues) as $index => $value) {
            $values[] = ($kept[$index] ?? false)
                ? $value
                : random_int(1, 6);
        }

        $roll = new DiceRoll($values);
        $throwCount++;

        $isWon = $this->strategy->isWinningRoll($roll);
        $isOver = $isWon || $throwCount >= $this->strategy->maxThrows();

        return new DiceThrowResult(
            roll: $roll,
            combination: DiceCombinationEvaluator::detect($roll),
            throwCount: $throwCount,
            isWon: $isWon,
            isOver: $isOver,
        );
    }
}
