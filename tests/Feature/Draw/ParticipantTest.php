<?php

use App\Domain\Draw\ValueObjects\Participant;

it('trims surrounding whitespace from the participant name', function () {
    $participant = new Participant('  Alice  ');

    expect($participant->name)->toBe('Alice');
});

it('defaults to a weight of one when none is given', function () {
    $participant = new Participant('Alice');

    expect($participant->weight)->toBe(1);
});

it('refuses an empty name, even if it only contains whitespace', function (string $name) {
    new Participant($name);
})->with(['', '   ', "\t\n"])->throws(InvalidArgumentException::class);

it('refuses a non-positive weight', function (int $weight) {
    new Participant('Alice', weight: $weight);
})->with([0, -1, -100])->throws(InvalidArgumentException::class);

it('accepts any strictly positive weight', function (int $weight) {
    $participant = new Participant('Alice', weight: $weight);

    expect($participant->weight)->toBe($weight);
})->with([1, 2, 50, 100]);
