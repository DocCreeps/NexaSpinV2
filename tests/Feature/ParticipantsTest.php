<?php

use App\Domain\Draw\Collections\Participants;
use App\Domain\Draw\Exceptions\InvalidDrawException;
use App\Domain\Draw\ValueObjects\Participant;

it('counts its items', function () {
    $participants = new Participants([
        new Participant('John'),
        new Participant('Jane'),
    ]);

    expect($participants)->toHaveCount(2)
        ->and($participants->count())->toBe(2);
});

it('is iterable in insertion order', function () {
    $participants = new Participants([
        new Participant('John'),
        new Participant('Jane'),
    ]);

    $names = [];

    foreach ($participants as $participant) {
        $names[] = $participant->name;
    }

    expect($names)->toBe(['John', 'Jane']);
});

it('returns the first participant', function () {
    $participants = new Participants([
        new Participant('John'),
        new Participant('Jane'),
    ]);

    expect($participants->first()->name)->toBe('John');
});

it('throws when getting the first participant of an empty collection', function () {
    (new Participants([]))->first();
})->throws(InvalidDrawException::class, 'Cannot get first participant from empty collection.');

it('returns a random participant among the given ones', function () {
    $names = ['John', 'Jane', 'Bob'];
    $participants = new Participants(array_map(fn (string $n) => new Participant($n), $names));

    $winner = $participants->random();

    expect($names)->toContain($winner->name);
});

it('throws when picking a random participant from an empty collection', function () {
    (new Participants([]))->random();
})->throws(InvalidDrawException::class, 'Cannot select random participant from empty collection.');

it('exposes all its items as a plain array', function () {
    $john = new Participant('John');
    $jane = new Participant('Jane');

    $participants = new Participants([$john, $jane]);

    expect($participants->all())->toBe([$john, $jane]);
});

it('rejects items that are not Participant instances', function () {
    // @phpstan-ignore-next-line - on force volontairement un type invalide
    new Participants(['not-a-participant']);
})->throws(InvalidDrawException::class, 'Invalid participant collection.');
