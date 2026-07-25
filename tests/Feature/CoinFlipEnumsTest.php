<?php

use App\Domain\CoinFlip\Enums\CoinSide;

it('exposes exactly the two CoinSide cases', function () {
    expect(CoinSide::cases())->toHaveCount(2)
        ->and(array_column(CoinSide::cases(), 'value'))->toBe(['pile', 'face']);
});

it('labels CoinSide cases in French', function () {
    expect(CoinSide::PILE->label())->toBe('Pile')
        ->and(CoinSide::FACE->label())->toBe('Face');
});

it('resolves CoinSide from its raw string value', function () {
    expect(CoinSide::tryFrom('pile'))->toBe(CoinSide::PILE)
        ->and(CoinSide::tryFrom('face'))->toBe(CoinSide::FACE)
        ->and(CoinSide::tryFrom('unknown'))->toBeNull();
});
