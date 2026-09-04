<?php

use App\Application\Tombola\Actions\DrawLotAction;

it('removes the winner from the pool when duplicates are not allowed', function () {
    $action = new DrawLotAction();

    $result = $action->execute(
        remainingPool: ['Alice', 'Bob', 'Charlie'],
        remainingWeights: [1, 1, 1],
        allowDuplicates: false,
    );

    expect(['Alice', 'Bob', 'Charlie'])->toContain($result->winner)
        ->and($result->remainingPool)->toHaveCount(2)
        ->and($result->remainingPool)->not->toContain($result->winner);
});

it('picks the only remaining participant when the pool has a single entry', function () {
    $action = new DrawLotAction();

    $result = $action->execute(
        remainingPool: ['Alice'],
        remainingWeights: [1],
        allowDuplicates: false,
    );

    expect($result->winner)->toBe('Alice')
        ->and($result->remainingPool)->toBe([]);
});

it('decrements the weight but keeps the participant when duplicates are allowed and weight remains', function () {
    $action = new DrawLotAction();

    $result = $action->execute(
        remainingPool: ['Alice'],
        remainingWeights: [3],
        allowDuplicates: true,
    );

    expect($result->winner)->toBe('Alice')
        ->and($result->remainingPool)->toBe(['Alice'])
        ->and($result->remainingWeights)->toBe([2]);
});

it('removes the participant once their weight reaches zero even with duplicates allowed', function () {
    $action = new DrawLotAction();

    $result = $action->execute(
        remainingPool: ['Alice'],
        remainingWeights: [1],
        allowDuplicates: true,
    );

    expect($result->winner)->toBe('Alice')
        ->and($result->remainingPool)->toBe([]);
});

it('always draws a name that belongs to the given pool', function () {
    $action = new DrawLotAction();

    for ($i = 0; $i < 20; $i++) {
        $result = $action->execute(
            remainingPool: ['Alice', 'Bob', 'Charlie', 'Dana'],
            remainingWeights: [1, 5, 2, 1],
            allowDuplicates: false,
        );

        expect(['Alice', 'Bob', 'Charlie', 'Dana'])->toContain($result->winner);
    }
});

it('refuses to draw from an empty pool', function () {
    $action = new DrawLotAction();

    $action->execute(remainingPool: [], remainingWeights: [], allowDuplicates: false);
})->throws(InvalidArgumentException::class);
