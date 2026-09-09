<?php

use App\Application\RockPaperScissors\Actions\PlayRpsRoundAction;
use App\Domain\RockPaperScissors\Enums\RpsChoice;
use App\Domain\RockPaperScissors\Enums\RpsOutcome;
use App\Domain\RockPaperScissors\Strategies\RandomRpsStrategy;
use App\Domain\RockPaperScissors\ValueObjects\RpsResult;

it('declares rock beats scissors', function () {
    expect(RpsChoice::ROCK->beats(RpsChoice::SCISSORS))->toBeTrue()
        ->and(RpsChoice::SCISSORS->beats(RpsChoice::ROCK))->toBeFalse();
});

it('declares paper beats rock', function () {
    expect(RpsChoice::PAPER->beats(RpsChoice::ROCK))->toBeTrue();
});

it('declares scissors beats paper', function () {
    expect(RpsChoice::SCISSORS->beats(RpsChoice::PAPER))->toBeTrue();
});

it('computes a draw outcome when both choices are identical', function () {
    $result = new RpsResult(RpsChoice::ROCK, RpsChoice::ROCK);

    expect($result->outcome)->toBe(RpsOutcome::DRAW);
});

it('computes a win outcome when the player choice beats the opponent', function () {
    $result = new RpsResult(RpsChoice::ROCK, RpsChoice::SCISSORS);

    expect($result->outcome)->toBe(RpsOutcome::WIN);
});

it('computes a lose outcome when the opponent choice beats the player', function () {
    $result = new RpsResult(RpsChoice::SCISSORS, RpsChoice::ROCK);

    expect($result->outcome)->toBe(RpsOutcome::LOSE);
});

it('always returns one of the three known choices from the random strategy', function () {
    $strategy = new RandomRpsStrategy;

    for ($i = 0; $i < 30; $i++) {
        expect($strategy->choose())->toBeIn(RpsChoice::cases());
    }
});

it('eventually returns every possible choice from the random strategy', function () {
    $strategy = new RandomRpsStrategy;
    $seen = [];

    for ($i = 0; $i < 200; $i++) {
        $seen[$strategy->choose()->value] = true;
    }

    expect(array_keys($seen))->toEqualCanonicalizing(
        array_map(fn (RpsChoice $c) => $c->value, RpsChoice::cases())
    );
});

it('delegates the round to the injected opponent strategy', function () {
    $action = new PlayRpsRoundAction(new RandomRpsStrategy);

    $result = $action->execute(RpsChoice::PAPER);

    expect($result)->toBeInstanceOf(RpsResult::class)
        ->and($result->player)->toBe(RpsChoice::PAPER)
        ->and($result->opponent)->toBeIn(RpsChoice::cases());
});
