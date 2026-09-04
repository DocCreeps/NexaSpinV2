<?php

use App\Domain\Draw\Collections\Participants;
use App\Domain\Draw\Exceptions\InvalidDrawException;
use App\Domain\Draw\ValueObjects\Participant;

function drawParticipants(array $names): Participants
{
    return new Participants(array_map(fn (string $name) => new Participant($name), $names));
}

it('counts its participants', function () {
    expect(drawParticipants(['Alice', 'Bob', 'Charlie'])->count())->toBe(3);
});

it('rejects anything that is not a Participant value object', function () {
    new Participants(['Alice', 'Bob']);
})->throws(InvalidDrawException::class);

it('refuses to pick a random participant from an empty collection', function () {
    (new Participants([]))->random();
})->throws(InvalidDrawException::class);

it('refuses to return a first participant from an empty collection', function () {
    (new Participants([]))->first();
})->throws(InvalidDrawException::class);

it('always returns a participant that was actually part of the collection', function () {
    $participants = drawParticipants(['Alice', 'Bob', 'Charlie']);
    $names = array_map(fn (Participant $p) => $p->name, $participants->all());

    for ($i = 0; $i < 50; $i++) {
        expect($names)->toContain($participants->random()->name);
    }
});

it('can eventually pick every participant when drawn many times (no participant is unreachable)', function () {
    $participants = drawParticipants(['Alice', 'Bob', 'Charlie']);
    $seen = [];

    for ($i = 0; $i < 500; $i++) {
        $seen[$participants->random()->name] = true;
    }

    expect(array_keys($seen))->toEqualCanonicalizing(['Alice', 'Bob', 'Charlie']);
});

it('is iterable over its participants in insertion order', function () {
    $participants = drawParticipants(['Alice', 'Bob']);

    $names = [];
    foreach ($participants as $participant) {
        $names[] = $participant->name;
    }

    expect($names)->toBe(['Alice', 'Bob']);
});
