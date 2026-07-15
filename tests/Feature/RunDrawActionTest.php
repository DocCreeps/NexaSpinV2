<?php

use App\Application\Draw\Actions\RunDrawAction;
use App\Application\Draw\DTOs\DrawData;
use App\Application\Draw\Resolvers\DrawStrategyResolver;
use App\Domain\Draw\Enums\DrawDisplay;
use App\Domain\Draw\Enums\DrawType;
use App\Domain\Draw\Exceptions\DrawTypeNotSupportedException;
use App\Domain\Draw\Strategies\RandomDrawStrategy;
use App\Domain\Draw\ValueObjects\DrawResult;

it('runs a random draw and returns one of the given participants', function () {
    $action = new RunDrawAction(new DrawStrategyResolver(new RandomDrawStrategy));

    // DrawData::participants attend des noms (string[]), pas des objets
    // Participant : c'est participantsCollection() qui se charge de les
    // transformer en Participant.
    $result = $action->execute(
        new DrawData(
            participants: ['John', 'Jane', 'Bob'],
            type: DrawType::RANDOM,
            display: DrawDisplay::SIMPLE,
        )
    );

    expect($result)->toBeInstanceOf(DrawResult::class)
        ->and(['John', 'Jane', 'Bob'])->toContain($result->winner->name);
});

it('throws a domain exception for an unsupported draw type', function () {
    $action = new RunDrawAction(new DrawStrategyResolver(new RandomDrawStrategy));

    $action->execute(
        new DrawData(
            participants: ['John', 'Jane'],
            type: DrawType::WEIGHTED,
            display: DrawDisplay::SIMPLE,
        )
    );
})->throws(DrawTypeNotSupportedException::class);
