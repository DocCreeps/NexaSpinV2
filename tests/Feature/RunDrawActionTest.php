<?php

use App\Application\Draw\Actions\RunDrawAction;
use App\Application\Draw\DTOs\DrawData;
use App\Domain\Draw\Enums\DrawDisplay;
use App\Domain\Draw\Enums\DrawType;
use App\Domain\Draw\ValueObjects\DrawResult;
use App\Domain\Draw\Strategies\RandomDrawStrategy;
use App\Application\Draw\Resolvers\DrawStrategyResolver;

it('runs a random draw and returns one of the given participants', function () {
    $action = new RunDrawAction(new DrawStrategyResolver(new RandomDrawStrategy()));

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

it('currently throws UnhandledMatchError for an unsupported draw type (known bug)', function () {
    // Comportement réel actuel, pas le comportement souhaité : voir
    // DrawFactoryTest et README > Limites connues. DrawTypeNotSupportedException
    // existe bien dans le domaine mais n'est levée nulle part sur ce chemin
    // puisque DrawFactory ne gère pas DrawType::WEIGHTED.
    $action = new RunDrawAction(new DrawStrategyResolver(new RandomDrawStrategy()));

    $action->execute(
        new DrawData(
            participants: ['John', 'Jane'],
            type: DrawType::WEIGHTED,
            display: DrawDisplay::SIMPLE,
        )
    );
})->throws(UnhandledMatchError::class);
