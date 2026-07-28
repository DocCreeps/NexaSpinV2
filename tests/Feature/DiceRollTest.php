<?php

use App\Domain\Dice\ValueObjects\DiceRoll;

it('wraps the dice values', function () {
    $roll = new DiceRoll([4, 2, 1]);

    expect($roll->values)->toBe([4, 2, 1]);
});

it('is immutable (readonly)', function () {
    $roll = new DiceRoll([4, 2, 1]);

    expect(fn () => $roll->values = [1, 1, 1])->toThrow(Error::class);
});

it('returns the values sorted in ascending order without mutating the original', function () {
    $roll = new DiceRoll([4, 2, 1]);

    expect($roll->sorted())->toBe([1, 2, 4])
        ->and($roll->values)->toBe([4, 2, 1]);
});

it('sorts an already ordered roll without changes', function () {
    $roll = new DiceRoll([1, 2, 3]);

    expect($roll->sorted())->toBe([1, 2, 3]);
});

it('sorts a roll with duplicate values', function () {
    $roll = new DiceRoll([6, 3, 6]);

    expect($roll->sorted())->toBe([3, 6, 6]);
});
