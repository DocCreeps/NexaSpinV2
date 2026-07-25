<?php

use App\Domain\CoinFlip\Enums\CoinSide;
use App\Domain\CoinFlip\ValueObjects\CoinFlipResult;

it('wraps the winning side', function () {
    $result = new CoinFlipResult(CoinSide::PILE);

    expect($result->side)->toBe(CoinSide::PILE);
});

it('is immutable (readonly)', function () {
    $result = new CoinFlipResult(CoinSide::PILE);

    expect(fn () => $result->side = CoinSide::FACE)->toThrow(Error::class);
});
