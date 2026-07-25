<?php

use App\Domain\CoinFlip\Enums\CoinSide;
use App\Domain\CoinFlip\ValueObjects\CoinFlipBet;
use App\Domain\CoinFlip\ValueObjects\CoinFlipResult;

it('is won when the chosen side matches the flip result', function () {
    $bet = new CoinFlipBet(CoinSide::PILE, new CoinFlipResult(CoinSide::PILE));

    expect($bet->won())->toBeTrue();
});

it('is lost when the chosen side does not match the flip result', function () {
    $bet = new CoinFlipBet(CoinSide::PILE, new CoinFlipResult(CoinSide::FACE));

    expect($bet->won())->toBeFalse();
});

it('is immutable (readonly)', function () {
    $bet = new CoinFlipBet(CoinSide::PILE, new CoinFlipResult(CoinSide::PILE));

    expect(fn () => $bet->chosen = CoinSide::FACE)->toThrow(Error::class);
});
