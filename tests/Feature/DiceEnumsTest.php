<?php

use App\Domain\Dice\Enums\DiceCombination;

it('exposes exactly the four DiceCombination cases', function () {
    expect(DiceCombination::cases())->toHaveCount(4)
        ->and(array_column(DiceCombination::cases(), 'value'))
        ->toBe(['four_two_one', 'brelan', 'suite', 'none']);
});

it('labels DiceCombination cases in French', function () {
    expect(DiceCombination::FOUR_TWO_ONE->label())->toBe('421')
        ->and(DiceCombination::BRELAN->label())->toBe('Brelan')
        ->and(DiceCombination::SUITE->label())->toBe('Suite')
        ->and(DiceCombination::NONE->label())->toBe('Aucune combinaison');
});

it('resolves DiceCombination from its raw string value', function () {
    expect(DiceCombination::tryFrom('brelan'))->toBe(DiceCombination::BRELAN)
        ->and(DiceCombination::tryFrom('unknown'))->toBeNull();
});
