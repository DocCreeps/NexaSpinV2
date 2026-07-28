<?php

use App\Application\Dice\Actions\RollDiceAction;
use App\Domain\Dice\Enums\DiceCombination;
use App\Domain\Dice\Strategies\FourTwoOneStrategy;
use App\Domain\Dice\ValueObjects\DiceThrowResult;

/**
 * En gardant les 3 dés (kept = [true, true, true]), RollDiceAction ne relance
 * aucune valeur : le résultat devient entièrement déterministe, sans dépendre
 * de random_int(). C'est ce qui permet de tester la détection de combinaison,
 * le calcul de isWon/isOver, etc. sans mock ni statistique.
 */
function rollWithFixedDice(array $values, int $throwCount = 0): DiceThrowResult
{
    $action = new RollDiceAction(new FourTwoOneStrategy);

    return $action->execute($values, [true, true, true], $throwCount);
}

it('keeps dice values unchanged when all are marked as kept', function () {
    $result = rollWithFixedDice([4, 2, 1]);

    expect($result->roll->values)->toBe([4, 2, 1]);
});

it('rerolls dice that are not kept within the 1-6 range', function () {
    $action = new RollDiceAction(new FourTwoOneStrategy);

    $result = $action->execute([1, 1, 1], [false, false, false], 0);

    foreach ($result->roll->values as $value) {
        expect($value)->toBeGreaterThanOrEqual(1)->toBeLessThanOrEqual(6);
    }
});

it('rerolls only the dice not marked as kept', function () {
    $action = new RollDiceAction(new FourTwoOneStrategy);

    $result = $action->execute([6, 6, 6], [true, false, true], 0);

    expect($result->roll->values[0])->toBe(6)
        ->and($result->roll->values[2])->toBe(6);
});

it('treats a missing kept entry as not kept (defaults to a reroll)', function () {
    $action = new RollDiceAction(new FourTwoOneStrategy);

    $result = $action->execute([6, 6, 6], [true, true], 0);

    expect($result->roll->values)->toHaveCount(3)
        ->and($result->roll->values[0])->toBe(6)
        ->and($result->roll->values[1])->toBe(6)
        ->and($result->roll->values[2])->toBeGreaterThanOrEqual(1)->toBeLessThanOrEqual(6);
});

it('increments the throw count', function () {
    $result = rollWithFixedDice([1, 3, 6], throwCount: 2);

    expect($result->throwCount)->toBe(3);
});

it('detects the winning combination and marks the game as won and over', function () {
    $result = rollWithFixedDice([4, 2, 1], throwCount: 0);

    expect($result->combination)->toBe(DiceCombination::FOUR_TWO_ONE)
        ->and($result->isWon)->toBeTrue()
        ->and($result->isOver)->toBeTrue();
});

it('is not over when the roll does not win and the throw limit is not reached', function () {
    $result = rollWithFixedDice([1, 3, 6], throwCount: 0);

    expect($result->isWon)->toBeFalse()
        ->and($result->isOver)->toBeFalse();
});

it('is over once the maximum number of throws is reached, even without winning', function () {
    $result = rollWithFixedDice([1, 3, 6], throwCount: 2);

    expect($result->throwCount)->toBe(3)
        ->and($result->isWon)->toBeFalse()
        ->and($result->isOver)->toBeTrue();
});

it('works end-to-end with real random rerolls and always returns a valid roll', function () {
    $action = new RollDiceAction(new FourTwoOneStrategy);

    $result = $action->execute([1, 1, 1], [false, false, false], 0);

    expect($result)->toBeInstanceOf(DiceThrowResult::class)
        ->and($result->roll->values)->toHaveCount(3);
});
