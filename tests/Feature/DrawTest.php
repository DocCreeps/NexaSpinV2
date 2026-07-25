<?php

use App\Domain\Draw\Collections\Participants;
use App\Domain\Draw\Contracts\DrawStrategy;
use App\Domain\Draw\Entities\Draw;
use App\Domain\Draw\Exceptions\InvalidDrawException;
use App\Domain\Draw\Strategies\RandomDrawStrategy;
use App\Domain\Draw\ValueObjects\DrawResult;
use App\Domain\Draw\ValueObjects\Participant;

// Draw est instanciée par RunDrawAction (voir RunDrawActionTest), qui construit
// l'entité pour faire appliquer ses invariants (min. 2 participants) avant de
// déléguer le tirage à la stratégie via Double Dispatch. On la teste aussi ici,
// isolément, puisqu'elle porte cette règle métier propre au Domain.

it('requires at least two participants', function () {
    new Draw(new Participants([new Participant('Solo')]));
})->throws(InvalidDrawException::class, 'A draw requires at least two participants.');

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

    $result = $draw->execute(new RandomDrawStrategy);

    expect($names)->toContain($result->winner->name);
});
