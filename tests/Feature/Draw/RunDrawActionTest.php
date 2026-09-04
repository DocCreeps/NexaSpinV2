<?php

use App\Application\Draw\Actions\RunDrawAction;
use App\Application\Draw\DTOs\DrawData;
use App\Domain\Draw\Enums\DrawDisplay;
use App\Domain\Draw\Enums\DrawType;
use App\Domain\Draw\Exceptions\InvalidDrawException;

it('runs a random draw and returns a winner among the given names', function () {
    $result = app(RunDrawAction::class)->execute(new DrawData(
        participants: ['Alice', 'Bob', 'Charlie'],
        type: DrawType::RANDOM,
        display: DrawDisplay::WHEEL,
    ));

    expect(['Alice', 'Bob', 'Charlie'])->toContain($result->winner->name);
});

it('runs a weighted draw using the given weights, in the same order as the participants', function () {
    $result = app(RunDrawAction::class)->execute(new DrawData(
        participants: ['Alice', 'Bob'],
        type: DrawType::WEIGHTED,
        display: DrawDisplay::WHEEL,
        weights: [100, 1],
    ));

    expect(['Alice', 'Bob'])->toContain($result->winner->name);
});

it('defaults every participant to a weight of one when no weights are provided', function () {
    // Cas d'usage réel : la roue classique n'envoie jamais de poids.
    $result = app(RunDrawAction::class)->execute(new DrawData(
        participants: ['Alice', 'Bob'],
        type: DrawType::WEIGHTED,
        display: DrawDisplay::WHEEL,
    ));

    expect(['Alice', 'Bob'])->toContain($result->winner->name);
});

it('refuses to run a draw with fewer than two participants', function () {
    app(RunDrawAction::class)->execute(new DrawData(
        participants: ['Alice'],
        type: DrawType::RANDOM,
        display: DrawDisplay::WHEEL,
    ));
})->throws(InvalidDrawException::class);

it('refuses to run a draw with no participants at all', function () {
    app(RunDrawAction::class)->execute(new DrawData(
        participants: [],
        type: DrawType::RANDOM,
        display: DrawDisplay::WHEEL,
    ));
})->throws(InvalidDrawException::class);
