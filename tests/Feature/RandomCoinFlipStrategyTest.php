<?php

use App\Domain\CoinFlip\Enums\CoinSide;
use App\Domain\CoinFlip\Strategies\RandomCoinFlipStrategy;
use App\Domain\CoinFlip\ValueObjects\CoinFlipResult;

it('returns a valid coin side', function () {
    $result = (new RandomCoinFlipStrategy)->flip();

    expect($result)->toBeInstanceOf(CoinFlipResult::class)
        ->and([CoinSide::PILE, CoinSide::FACE])->toContain($result->side);
});

it('produces a roughly even 50/50 distribution over many flips', function () {
    $strategy = new RandomCoinFlipStrategy;

    $counts = [CoinSide::PILE->value => 0, CoinSide::FACE->value => 0];

    for ($i = 0; $i < 1000; $i++) {
        $counts[$strategy->flip()->side->value]++;
    }

    // Marge large pour éviter un test flaky (pas une assertion statistique
    // stricte) : sur 1000 tirages équiprobables, une face sous les 350
    // occurrences serait extraordinairement improbable.
    expect($counts[CoinSide::PILE->value])->toBeGreaterThan(350)
        ->and($counts[CoinSide::FACE->value])->toBeGreaterThan(350);
});
