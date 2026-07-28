<?php

use App\Domain\Dice\Strategies\FourTwoOneStrategy;
use App\Domain\Dice\ValueObjects\DiceRoll;

it('uses exactly 3 dice', function () {
    expect((new FourTwoOneStrategy)->diceCount())->toBe(3);
});

it('allows exactly 3 throws', function () {
    expect((new FourTwoOneStrategy)->maxThrows())->toBe(3);
});

it('considers a 4-2-1 roll as winning', function () {
    $strategy = new FourTwoOneStrategy;

    expect($strategy->isWinningRoll(new DiceRoll([4, 2, 1])))->toBeTrue()
        ->and($strategy->isWinningRoll(new DiceRoll([1, 2, 4])))->toBeTrue();
});

it('does not consider a brelan or a suite as winning', function () {
    $strategy = new FourTwoOneStrategy;

    expect($strategy->isWinningRoll(new DiceRoll([5, 5, 5])))->toBeFalse()
        ->and($strategy->isWinningRoll(new DiceRoll([2, 3, 4])))->toBeFalse()
        ->and($strategy->isWinningRoll(new DiceRoll([1, 3, 6])))->toBeFalse();
});
