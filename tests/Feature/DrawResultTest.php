<?php

use App\Domain\Draw\ValueObjects\DrawResult;
use App\Domain\Draw\ValueObjects\Participant;

it('wraps the winning participant', function () {
    $winner = new Participant('John');
    $result = new DrawResult($winner);

    expect($result->winner)->toBe($winner)
        ->and($result->winner->name)->toBe('John');
});

it('is immutable (readonly)', function () {
    $result = new DrawResult(new Participant('John'));

    expect(fn () => $result->winner = new Participant('Jane'))->toThrow(Error::class);
});
