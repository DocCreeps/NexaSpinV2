<?php

use App\Application\Draw\Actions\RunDrawAction;
use App\Application\Draw\DTOs\DrawData;
use App\Application\Draw\Resolvers\DrawStrategyResolver;
use App\Domain\Draw\Enums\DrawDisplay;
use App\Domain\Draw\Enums\DrawType;
use App\Domain\Draw\Exceptions\InvalidDrawException;
use App\Domain\Draw\Strategies\RandomDrawStrategy;
use App\Domain\Draw\Strategies\WeightedDrawStrategy;
use App\Domain\Draw\ValueObjects\DrawResult;

it('runs a random draw and returns one of the given participants', function () {
    $action = new RunDrawAction(new DrawStrategyResolver(new RandomDrawStrategy, new WeightedDrawStrategy));

    $result = $action->execute(
        new DrawData(
            participants: ['John', 'Jane', 'Jack'],
            type: DrawType::RANDOM,
            display: DrawDisplay::SIMPLE,
        )
    );

    expect($result)->toBeInstanceOf(DrawResult::class)
        ->and(['John', 'Jane', 'Jack'])->toContain($result->winner->name);
});

it('runs a weighted draw and returns one of the given participants', function () {
    $action = new RunDrawAction(new DrawStrategyResolver(new RandomDrawStrategy, new WeightedDrawStrategy));

    $result = $action->execute(
        new DrawData(
            participants: ['John', 'Jane'],
            type: DrawType::WEIGHTED,
            display: DrawDisplay::SIMPLE,
            weights: [1, 5],
        )
    );

    expect($result)->toBeInstanceOf(DrawResult::class)
        ->and(['John', 'Jane'])->toContain($result->winner->name);
});

it('throws when trying to draw with fewer than two participants', function () {
    $action = new RunDrawAction(new DrawStrategyResolver(new RandomDrawStrategy, new WeightedDrawStrategy));

    $action->execute(
        new DrawData(
            participants: ['John'],
            type: DrawType::RANDOM,
            display: DrawDisplay::SIMPLE,
        )
    );
})->throws(InvalidDrawException::class);
