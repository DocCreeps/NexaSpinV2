<?php

use App\Application\Dice\Actions\RollDiceAction;
use App\Domain\Dice\Enums\DiceCombination;
use App\Domain\Dice\Strategies\FourTwoOneStrategy;
use App\Domain\Dice\Support\DiceCombinationEvaluator;
use App\Domain\Dice\ValueObjects\DiceRoll;

it('detects the 421 combination regardless of dice order', function ($values) {
    $combination = DiceCombinationEvaluator::detect(new DiceRoll($values));

    expect($combination)->toBe(DiceCombination::FOUR_TWO_ONE);
})->with([
    [[4, 2, 1]],
    [[1, 2, 4]],
    [[2, 4, 1]],
    [[1, 4, 2]],
]);

it('detects a brelan (three identical dice)', function () {
    $combination = DiceCombinationEvaluator::detect(new DiceRoll([5, 5, 5]));

    expect($combination)->toBe(DiceCombination::BRELAN);
});

it('detects a suite (three consecutive values)', function ($values) {
    $combination = DiceCombinationEvaluator::detect(new DiceRoll($values));

    expect($combination)->toBe(DiceCombination::SUITE);
})->with([
    [[2, 3, 4]],
    [[4, 3, 2]],
    [[5, 6, 4]],
]);

it('detects no combination for an unrelated roll', function () {
    $combination = DiceCombinationEvaluator::detect(new DiceRoll([1, 3, 6]));

    expect($combination)->toBe(DiceCombination::NONE);
});

it('exposes the classic 421 rules (3 dice, 3 throws)', function () {
    $strategy = new FourTwoOneStrategy();

    expect($strategy->diceCount())->toBe(3)
        ->and($strategy->maxThrows())->toBe(3);
});

it('considers a 4-2-1 roll a winning roll for the 421 strategy', function () {
    $strategy = new FourTwoOneStrategy();

    expect($strategy->isWinningRoll(new DiceRoll([4, 2, 1])))->toBeTrue();
});

it('does not consider any other roll a winning roll for the 421 strategy', function () {
    $strategy = new FourTwoOneStrategy();

    expect($strategy->isWinningRoll(new DiceRoll([5, 5, 5])))->toBeFalse()
        ->and($strategy->isWinningRoll(new DiceRoll([2, 3, 4])))->toBeFalse();
});

it('rerolls only the dice that were not kept', function () {
    $action = new RollDiceAction(new FourTwoOneStrategy());

    $result = $action->execute(
        currentValues: [6, 6, 6],
        kept: [true, false, false],
        throwCount: 0,
    );

    expect($result->roll->values[0])->toBe(6);
});

it('ends the game as soon as a winning roll (421) is thrown', function () {
    $action = new RollDiceAction(new FourTwoOneStrategy());

    $result = $action->execute(
        currentValues: [4, 2, 1],
        kept: [true, true, true],
        throwCount: 1,
    );

    expect($result->isWon)->toBeTrue()
        ->and($result->isOver)->toBeTrue()
        ->and($result->combination)->toBe(DiceCombination::FOUR_TWO_ONE);
});

it('ends the game after reaching the maximum number of throws even without winning', function () {
    $action = new RollDiceAction(new FourTwoOneStrategy());

    // Third throw (throwCount goes from 2 to 3, the strategy's max), with dice
    // kept so the roll deterministically avoids a win.
    $result = $action->execute(
        currentValues: [3, 3, 3],
        kept: [true, true, true],
        throwCount: 2,
    );

    expect($result->throwCount)->toBe(3)
        ->and($result->isOver)->toBeTrue()
        ->and($result->isWon)->toBeFalse();
});

it('does not end the game before the winning roll or the throw limit', function () {
    $action = new RollDiceAction(new FourTwoOneStrategy());

    $result = $action->execute(
        currentValues: [3, 3, 3],
        kept: [true, true, true],
        throwCount: 0,
    );

    expect($result->throwCount)->toBe(1)
        ->and($result->isOver)->toBeFalse();
});
