<?php

use App\Domain\Draw\Collections\Participants;
use App\Domain\Draw\Contracts\DrawStrategy;
use App\Domain\Draw\Entities\Draw;
use App\Domain\Draw\Exceptions\InvalidDrawException;
use App\Domain\Draw\Strategies\RandomDrawStrategy;
use App\Domain\Draw\ValueObjects\DrawResult;
use App\Domain\Draw\ValueObjects\Participant;

// NB: cette entité n'est actuellement appelée par aucun composant applicatif
// (RunDrawAction travaille directement avec Participants + une Strategy).
// On la teste quand même isolément puisqu'elle fait partie du Domain et
// porte une règle métier propre (minimum 2 participants).

it('requires at least two participants', function () {
    new Draw(new Participants([new Participant('Solo')]));
})->throws(InvalidDrawException::class, 'A draw requires at least two participants.');

it('rejects an empty participant list', function () {
    new Draw(new Participants([]));
})->throws(InvalidDrawException::class);

it('accepts exactly two participants', function () {
    $draw = new Draw(new Participants([
        new Participant('John'),
        new Participant('Jane'),
    ]));

    expect($draw->participants())->toHaveCount(2);
});

it('delegates the winner selection to the given strategy', function () {
    $participants = new Participants([
        new Participant('John'),
        new Participant('Jane'),
    ]);

    $draw = new Draw($participants);

    $fixedStrategy = new class implements DrawStrategy
    {
        public function draw(Participants $participants): DrawResult
        {
            return new DrawResult($participants->first());
        }
    };

    $result = $draw->execute($fixedStrategy);

    expect($result)->toBeInstanceOf(DrawResult::class)
        ->and($result->winner->name)->toBe('John');
});

it('works end-to-end with the real RandomDrawStrategy', function () {
    $names = ['John', 'Jane', 'Bob'];
    $draw = new Draw(new Participants(array_map(fn (string $n) => new Participant($n), $names)));

    $result = $draw->execute(new RandomDrawStrategy());

    expect($names)->toContain($result->winner->name);
});
