<?php

use App\Application\DiceRoller\Actions\RollDiceSetAction;
use App\Domain\DiceRoller\Enums\DiceFaceCount;
use App\Domain\DiceRoller\Exceptions\InvalidDiceRollException;

it('labels each die type after its number of faces', function () {
    expect(DiceFaceCount::D4->label())->toBe('d4')
        ->and(DiceFaceCount::D20->label())->toBe('d20')
        ->and(DiceFaceCount::D100->label())->toBe('d100');
});

it('rolls exactly the requested number of dice', function () {
    $result = (new RollDiceSetAction)->execute(DiceFaceCount::D6, 5);

    expect($result->count())->toBe(5)
        ->and($result->values)->toHaveCount(5);
});

it('never produces a value outside the die faces range', function (DiceFaceCount $faces) {
    $result = (new RollDiceSetAction)->execute($faces, 20);

    foreach ($result->values as $value) {
        expect($value)->toBeGreaterThanOrEqual(1)->toBeLessThanOrEqual($faces->value);
    }
})->with([
    DiceFaceCount::D4,
    DiceFaceCount::D6,
    DiceFaceCount::D8,
    DiceFaceCount::D10,
    DiceFaceCount::D12,
    DiceFaceCount::D20,
    DiceFaceCount::D100,
]);

it('sums the individual dice values correctly', function () {
    $result = (new RollDiceSetAction)->execute(DiceFaceCount::D6, 10);

    expect($result->sum())->toBe(array_sum($result->values));
});

it('refuses to roll fewer than one die', function () {
    (new RollDiceSetAction)->execute(DiceFaceCount::D6, 0);
})->throws(InvalidDiceRollException::class);

it('refuses to roll more than the maximum allowed dice', function () {
    (new RollDiceSetAction)->execute(DiceFaceCount::D6, 21);
})->throws(InvalidDiceRollException::class);

it('accepts the boundary dice counts of 1 and 20', function (int $count) {
    $result = (new RollDiceSetAction)->execute(DiceFaceCount::D6, $count);

    expect($result->count())->toBe($count);
})->with([1, 20]);

it('eventually rolls every possible value on a d6 across many rolls', function () {
    $action = new RollDiceSetAction;
    $values = [];

    for ($i = 0; $i < 200; $i++) {
        $result = $action->execute(DiceFaceCount::D6, 1);
        $values[] = $result->values[0];
    }

    $uniqueValues = array_values(array_unique($values));

    expect($uniqueValues)->toEqualCanonicalizing([1, 2, 3, 4, 5, 6]);
});
