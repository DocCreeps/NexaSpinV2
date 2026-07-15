<?php

use App\Domain\Draw\Collections\Participants;
use App\Domain\Draw\Exceptions\InvalidDrawException;
use App\Domain\Draw\Strategies\RandomDrawStrategy;
use App\Domain\Draw\ValueObjects\DrawResult;
use App\Domain\Draw\ValueObjects\Participant;

it('returns a winner among the given participants', function () {
    $names = ['John', 'Jane', 'Bob'];
    $participants = new Participants(array_map(fn (string $n) => new Participant($n), $names));

    $result = (new RandomDrawStrategy)->draw($participants);

    expect($result)->toBeInstanceOf(DrawResult::class)
        ->and($names)->toContain($result->winner->name);
});

it('throws when drawing from an empty collection', function () {
    (new RandomDrawStrategy)->draw(new Participants([]));
})->throws(InvalidDrawException::class);
