<?php

use App\Application\Draw\Actions\RunDrawAction;
use App\Application\Draw\DTOs\DrawData;
use App\Domain\Draw\Enums\DrawType;
use App\Domain\Draw\Exceptions\DrawTypeNotSupportedException;
use App\Domain\Draw\ValueObjects\Participant;

it('runs a random draw', function () {

    $action = new RunDrawAction();

    $result = $action->execute(
        new DrawData(
            participants: [
                new Participant('John'),
                new Participant('Jane'),
                new Participant('Bob'),
            ],
            type: DrawType::RANDOM,
        )
    );

    expect(['John', 'Jane', 'Bob'])->toContain($result->name);
});

it('throws when the draw type is not supported yet', function () {

    $action = new RunDrawAction();

    $action->execute(
        new DrawData(
            participants: [new Participant('John')],
            type: DrawType::WEIGHTED,
        )
    );

})->throws(DrawTypeNotSupportedException::class);
