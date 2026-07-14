<?php

use App\Domain\Draw\ValueObjects\Participant;

it('creates a participant with a default weight of one', function () {
    $participant = new Participant('John');

    expect($participant->name)->toBe('John')
        ->and($participant->weight)->toBe(1);
});

it('accepts a custom weight', function () {
    $participant = new Participant('John', 5);

    expect($participant->weight)->toBe(5);
});

it('rejects an empty name', function () {
    new Participant('');
})->throws(InvalidArgumentException::class, 'Participant name cannot be empty.');

it('rejects a name made only of whitespace', function () {
    new Participant('   ');
})->throws(InvalidArgumentException::class, 'Participant name cannot be empty.');

it('rejects a weight of zero', function () {
    new Participant('John', 0);
})->throws(InvalidArgumentException::class, 'Weight must be greater than zero.');

it('rejects a negative weight', function () {
    new Participant('John', -3);
})->throws(InvalidArgumentException::class);

it('is immutable (readonly)', function () {
    $participant = new Participant('John');

    expect(fn () => $participant->name = 'Jane')->toThrow(Error::class);
});
