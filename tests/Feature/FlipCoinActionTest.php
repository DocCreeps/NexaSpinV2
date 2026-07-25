<?php

use App\Application\CoinFlip\Actions\FlipCoinAction;
use App\Domain\CoinFlip\Contracts\CoinFlipStrategy;
use App\Domain\CoinFlip\Enums\CoinSide;
use App\Domain\CoinFlip\Strategies\RandomCoinFlipStrategy;
use App\Domain\CoinFlip\ValueObjects\CoinFlipResult;

it('delegates the flip to the given strategy and returns its result', function () {
    $expected = new CoinFlipResult(CoinSide::FACE);

    $fixedStrategy = new class($expected) implements CoinFlipStrategy
    {
        public function __construct(private readonly CoinFlipResult $result) {}

        public function flip(): CoinFlipResult
        {
            return $this->result;
        }
    };

    $action = new FlipCoinAction($fixedStrategy);

    expect($action->execute())->toBe($expected);
});

it('works end-to-end with the real RandomCoinFlipStrategy', function () {
    $action = new FlipCoinAction(new RandomCoinFlipStrategy);

    $result = $action->execute();

    expect($result)->toBeInstanceOf(CoinFlipResult::class)
        ->and([CoinSide::PILE, CoinSide::FACE])->toContain($result->side);
});
