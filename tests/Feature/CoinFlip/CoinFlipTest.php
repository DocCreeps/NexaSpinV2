<?php

use App\Application\CoinFlip\Actions\FlipCoinAction;
use App\Domain\CoinFlip\Enums\CoinSide;
use App\Domain\CoinFlip\Strategies\RandomCoinFlipStrategy;
use App\Domain\CoinFlip\ValueObjects\CoinFlipBet;
use App\Domain\CoinFlip\ValueObjects\CoinFlipResult;

it('always flips to one of the two known sides', function () {
    $strategy = new RandomCoinFlipStrategy();

    for ($i = 0; $i < 30; $i++) {
        $result = $strategy->flip();

        expect($result->side)->toBeIn([CoinSide::PILE, CoinSide::FACE]);
    }
});

it('flips both sides over many attempts (never always the same side)', function () {
    $strategy = new RandomCoinFlipStrategy();
    $sides = [];

    for ($i = 0; $i < 100; $i++) {
        $sides[] = $strategy->flip()->side->value;
    }

    expect(array_unique($sides))->toHaveCount(2);
});

it('declares a bet won when the chosen side matches the result', function () {
    $bet = new CoinFlipBet(CoinSide::PILE, new CoinFlipResult(CoinSide::PILE));

    expect($bet->won())->toBeTrue();
});

it('declares a bet lost when the chosen side does not match the result', function () {
    $bet = new CoinFlipBet(CoinSide::PILE, new CoinFlipResult(CoinSide::FACE));

    expect($bet->won())->toBeFalse();
});

it('delegates the flip to the injected strategy', function () {
    $action = new FlipCoinAction(new RandomCoinFlipStrategy());

    $result = $action->execute();

    expect($result)->toBeInstanceOf(CoinFlipResult::class)
        ->and($result->side)->toBeIn([CoinSide::PILE, CoinSide::FACE]);
});
