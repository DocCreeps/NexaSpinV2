<?php

use App\Application\Draw\DTOs\DrawData;
use App\Domain\Draw\Enums\DrawDisplay;
use App\Domain\Draw\Enums\DrawType;

it('converts plain names into a Participants collection with default weight 1', function () {
    $data = new DrawData(
        participants: ['Alice', 'Bob'],
        type: DrawType::RANDOM,
        display: DrawDisplay::WHEEL,
    );

    $participants = $data->participantsCollection()->all();

    expect($participants[0]->name)->toBe('Alice')
        ->and($participants[0]->weight)->toBe(1)
        ->and($participants[1]->name)->toBe('Bob')
        ->and($participants[1]->weight)->toBe(1);
});

it('applies the given weights to the matching participant by index', function () {
    $data = new DrawData(
        participants: ['Alice', 'Bob'],
        type: DrawType::WEIGHTED,
        display: DrawDisplay::WHEEL,
        weights: [5, 10],
    );

    $participants = $data->participantsCollection()->all();

    expect($participants[0]->weight)->toBe(5)
        ->and($participants[1]->weight)->toBe(10);
});

it('falls back to weight 1 for a participant missing from the weights array', function () {
    $data = new DrawData(
        participants: ['Alice', 'Bob'],
        type: DrawType::WEIGHTED,
        display: DrawDisplay::WHEEL,
        weights: [5],
    );

    $participants = $data->participantsCollection()->all();

    expect($participants[1]->weight)->toBe(1);
});
