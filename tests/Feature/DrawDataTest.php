<?php

use App\Application\Draw\DTOs\DrawData;
use App\Domain\Draw\Collections\Participants;
use App\Domain\Draw\Enums\DrawDisplay;
use App\Domain\Draw\Enums\DrawType;

it('converts raw participant names into a Participants collection, in order', function () {
    $data = new DrawData(
        participants: ['John', 'Jane', 'Bob'],
        type: DrawType::RANDOM,
        display: DrawDisplay::WHEEL,
    );

    $collection = $data->participantsCollection();

    expect($collection)->toBeInstanceOf(Participants::class)
        ->and($collection)->toHaveCount(3)
        ->and(collect($collection->all())->pluck('name')->all())->toBe(['John', 'Jane', 'Bob']);
});

it('gives every generated participant the default weight of one', function () {
    $data = new DrawData(['John'], DrawType::RANDOM, DrawDisplay::SIMPLE);

    expect($data->participantsCollection()->first()->weight)->toBe(1);
});

it('defaults options to an empty array', function () {
    $data = new DrawData(['John', 'Jane'], DrawType::RANDOM, DrawDisplay::SIMPLE);

    expect($data->options)->toBe([]);
});

it('accepts custom options', function () {
    $data = new DrawData(['John', 'Jane'], DrawType::RANDOM, DrawDisplay::SIMPLE, ['foo' => 'bar']);

    expect($data->options)->toBe(['foo' => 'bar']);
});
