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

it('blocks editing a match once its result has propagated to a resolved downstream match', function () {
    $bracket = new DoubleEliminationBracket(makeParticipants(8));

    foreach ($bracket->upperRounds()[1] as $match) {
        expect($bracket->hasDownstreamResult('upper', 1, $match->position))->toBeFalse();
    }

    foreach ($bracket->upperRounds()[1] as $match) {
        $bracket->recordUpperResult(1, $match->position, $match->participantA());
    }

    // Rien n'a encore été rejoué derrière : tout le round 1 reste éditable.
    foreach ($bracket->upperRounds()[1] as $match) {
        expect($bracket->hasDownstreamResult('upper', 1, $match->position))->toBeFalse();
    }

    // LB round 1 match 0 est nourri par les perdants de UB R1 position 0 ET 1
    // (voir DoubleEliminationBracket::dropLoserToLowerBracket) : le jouer
    // bloque donc l'édition de CES DEUX matchs UB R1, mais pas des autres.
    $lbMatch0 = $bracket->lowerRounds()[1][0];
    $bracket->recordLowerResult(1, 0, $lbMatch0->participantA());

    expect($bracket->hasDownstreamResult('upper', 1, 0))->toBeTrue()
        ->and($bracket->hasDownstreamResult('upper', 1, 1))->toBeTrue()
        ->and($bracket->hasDownstreamResult('upper', 1, 2))->toBeFalse()
        ->and($bracket->hasDownstreamResult('upper', 1, 3))->toBeFalse();

    // UB round 2 match 0 est nourri par les vainqueurs de UB R1 position 0 et 1 :
    // le jouer confirme leur blocage (déjà vrai) sans toucher aux positions 2/3.
    $bracket->recordUpperResult(2, 0, $bracket->upperRounds()[2][0]->participantA());

    expect($bracket->hasDownstreamResult('upper', 1, 2))->toBeFalse()
        ->and($bracket->hasDownstreamResult('upper', 1, 3))->toBeFalse();
});

it('reaches a champion for participant counts that are not a power of two, without ever throwing', function (int $count) {
    $bracket = new DoubleEliminationBracket(makeParticipants($count));

    // On joue systématiquement tout match jouable (slot A gagnant), jusqu'à
    // ce que plus aucune progression ne soit possible : le tournoi doit
    // atteindre un champion sans qu'aucun bye/match fantôme ne bloque le jeu.
    for ($safety = 0; $safety < 100; $safety++) {
        $playedSomething = false;

        foreach ($bracket->upperRounds() as $round => $matches) {
            foreach ($matches as $match) {
                if ($match->isPlayable()) {
                    $bracket->recordUpperResult($round, $match->position, $match->participantA());
                    $playedSomething = true;
                }
            }
        }

        foreach ($bracket->lowerRounds() as $round => $matches) {
            foreach ($matches as $match) {
                if ($match->isPlayable()) {
                    $bracket->recordLowerResult($round, $match->position, $match->participantA());
                    $playedSomething = true;
                }
            }
        }

        if ($bracket->grandFinal()?->isPlayable()) {
            $bracket->recordGrandFinalResult($bracket->grandFinal()->participantA());
            $playedSomething = true;
        }

        if ($bracket->grandFinalReset()?->isPlayable()) {
            $bracket->recordGrandFinalResetResult($bracket->grandFinalReset()->participantA());
            $playedSomething = true;
        }

        if ($bracket->isComplete() || ! $playedSomething) {
            break;
        }
    }

    expect($bracket->isComplete())->toBeTrue()
        ->and($bracket->champion())->not->toBeNull();
})->with([5, 6, 7, 9, 11, 13]);
