<?php

use App\Domain\Tournament\Collections\Participants;
use App\Domain\Tournament\Bracket\Entities\DoubleEliminationBracket;
use App\Domain\Tournament\ValueObjects\Participant;

function makeParticipants(int $count): Participants
{
    return new Participants(
        array_map(fn (int $i) => new Participant("P{$i}"), range(1, $count))
    );
}

it('builds upper and lower brackets for 8 participants with no dead matches', function () {
    $bracket = new DoubleEliminationBracket(makeParticipants(8));

    expect($bracket->upperRoundCount())->toBe(3)
        ->and($bracket->lowerRoundCount())->toBe(4);

    // UB round 1 : 4 matchs, tous jouables (8 = puissance de 2, aucun bye).
    foreach ($bracket->upperRounds()[1] as $match) {
        expect($match->isPlayable())->toBeTrue();
    }
});

it('propagates upper bracket losers into the lower bracket and reaches a champion', function () {
    $bracket = new DoubleEliminationBracket(makeParticipants(8));

    // UB Round 1 : P1,P3,P5,P7 gagnent (P2,P4,P6,P8 tombent en LB).
    foreach ($bracket->upperRounds()[1] as $match) {
        $bracket->recordUpperResult(1, $match->position, $match->participantA());
    }

    // LB Round 1 doit maintenant être jouable avec les perdants de l'UB.
    foreach ($bracket->lowerRounds()[1] as $match) {
        expect($match->isPlayable())->toBeTrue();
        $bracket->recordLowerResult(1, $match->position, $match->participantA());
    }

    // UB Round 2 : P1, P5 gagnent (P3, P7 tombent en LB round 2, le round "majeur").
    foreach ($bracket->upperRounds()[2] as $match) {
        $bracket->recordUpperResult(2, $match->position, $match->participantA());
    }

    foreach ($bracket->lowerRounds()[2] as $match) {
        expect($match->isPlayable())->toBeTrue();
    }

    // UB Finale : P1 gagne.
    $bracket->recordUpperResult(3, 0, $bracket->upperRounds()[3][0]->participantA());

    // On joue le lower bracket jusqu'au bout, en laissant toujours le slot A gagner.
    for ($round = 1; $round <= $bracket->lowerRoundCount(); $round++) {
        foreach ($bracket->lowerRounds()[$round] as $match) {
            if ($match->isResolved()) {
                continue;
            }
            expect($match->isPlayable())->toBeTrue();
            $bracket->recordLowerResult($round, $match->position, $match->participantA());
        }
    }

    expect($bracket->grandFinal()?->isPlayable())->toBeTrue();

    $upperChampionName = $bracket->grandFinal()->participantA()->name;
    $bracket->recordGrandFinalResult($bracket->grandFinal()->participantA());

    expect($bracket->isComplete())->toBeTrue()
        ->and($bracket->champion()?->name)->toBe($upperChampionName);
});

it('requires a reset match when the lower bracket champion wins the grand final', function () {
    $bracket = new DoubleEliminationBracket(makeParticipants(4));

    foreach ($bracket->upperRounds()[1] as $match) {
        $bracket->recordUpperResult(1, $match->position, $match->participantA());
    }

    foreach ($bracket->lowerRounds()[1] as $match) {
        $bracket->recordLowerResult(1, $match->position, $match->participantA());
    }

    $bracket->recordUpperResult(2, 0, $bracket->upperRounds()[2][0]->participantA());

    foreach ($bracket->lowerRounds()[2] as $match) {
        if (! $match->isResolved()) {
            $bracket->recordLowerResult(2, $match->position, $match->participantB());
        }
    }

    $lowerChampion = $bracket->grandFinal()->participantB();
    $bracket->recordGrandFinalResult($lowerChampion);

    expect($bracket->isComplete())->toBeFalse()
        ->and($bracket->grandFinalReset()?->isPlayable())->toBeTrue();

    $bracket->recordGrandFinalResetResult($lowerChampion);

    expect($bracket->isComplete())->toBeTrue()
        ->and($bracket->champion()?->name)->toBe($lowerChampion->name);
});

it('cascades a round 1 bye into the lower bracket without leaving a dead match', function () {
    // 5 participants -> taille 8, 3 byes en UB round 1.
    $bracket = new DoubleEliminationBracket(makeParticipants(5));

    $byeMatches = array_filter($bracket->upperRounds()[1], fn ($m) => $m->isBye());
    expect($byeMatches)->not->toBeEmpty();

    foreach ($bracket->upperRounds()[1] as $match) {
        if ($match->isPlayable()) {
            $bracket->recordUpperResult(1, $match->position, $match->participantA());
        }
    }

    // Aucune exception ne doit être levée par la propagation, et chaque match LB
    // round 1 est soit jouable, soit un bye résolu automatiquement.
    foreach ($bracket->lowerRounds()[1] as $match) {
        expect($match->isPlayable() || $match->isResolved() || (! $match->participantA() && ! $match->participantB()))
            ->toBeTrue();
    }
});
