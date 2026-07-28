<?php

use App\Domain\Dice\Enums\DiceCombination;
use App\Domain\Dice\Support\DiceCombinationEvaluator;
use App\Domain\Dice\ValueObjects\DiceRoll;

it('detects the 421 combination regardless of dice order', function () {
    expect(DiceCombinationEvaluator::detect(new DiceRoll([4, 2, 1])))->toBe(DiceCombination::FOUR_TWO_ONE)
        ->and(DiceCombinationEvaluator::detect(new DiceRoll([1, 2, 4])))->toBe(DiceCombination::FOUR_TWO_ONE)
        ->and(DiceCombinationEvaluator::detect(new DiceRoll([2, 4, 1])))->toBe(DiceCombination::FOUR_TWO_ONE);
});

it('detects a brelan (three identical dice)', function () {
    expect(DiceCombinationEvaluator::detect(new DiceRoll([5, 5, 5])))->toBe(DiceCombination::BRELAN)
        ->and(DiceCombinationEvaluator::detect(new DiceRoll([1, 1, 1])))->toBe(DiceCombination::BRELAN);
});

it('detects a suite (three consecutive values)', function () {
    expect(DiceCombinationEvaluator::detect(new DiceRoll([3, 2, 4])))->toBe(DiceCombination::SUITE)
        ->and(DiceCombinationEvaluator::detect(new DiceRoll([5, 6, 4])))->toBe(DiceCombination::SUITE);
});

it('does not classify 1-2-4 as a suite (421 takes precedence)', function () {
    // 421 est un cas particulier qui ressemble à une suite inversée-espacée
    // mais doit rester détecté comme FOUR_TWO_ONE et non comme SUITE/NONE.
    expect(DiceCombinationEvaluator::detect(new DiceRoll([1, 2, 4])))->toBe(DiceCombination::FOUR_TWO_ONE);
});

it('returns none for a roll with no recognized combination', function () {
    expect(DiceCombinationEvaluator::detect(new DiceRoll([1, 3, 6])))->toBe(DiceCombination::NONE)
        ->and(DiceCombinationEvaluator::detect(new DiceRoll([2, 2, 5])))->toBe(DiceCombination::NONE);
});
