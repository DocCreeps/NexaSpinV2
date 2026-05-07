<?php

use App\Application\Draw\Actions\RunDrawAction;
use App\Application\Draw\DTOs\DrawData;
use App\Domain\Draw\Enums\DrawType;

it('runs a random draw', function () {

    $action = new RunDrawAction();

    $result = $action->execute(
        new DrawData(
            participants: ['John', 'Jane', 'Bob'],
            type: DrawType::RANDOM,
        )
    );

    expect([
        'John',
        'Jane',
        'Bob',
    ])->toContain($result);
});
