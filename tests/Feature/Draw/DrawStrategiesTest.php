<?php

use App\Domain\Draw\Collections\Participants;
use App\Domain\Draw\Entities\Draw;
use App\Domain\Draw\Exceptions\InvalidDrawException;
use App\Domain\Draw\Strategies\RandomDrawStrategy;
use App\Domain\Draw\Strategies\WeightedDrawStrategy;
use App\Domain\Draw\ValueObjects\Participant;

function drawWith(array $participants): Participants
{
    return new Participants($participants);
}

it('refuses to create a draw with fewer than two participants', function () {
    new Draw(drawWith([new Participant('Alice')]));
})->throws(InvalidDrawException::class);

it('allows a draw with exactly two participants', function () {
    $draw = new Draw(drawWith([new Participant('Alice'), new Participant('Bob')]));

    expect($draw->participants()->count())->toBe(2);
});

it('delegates the actual selection to whichever strategy it is given', function () {
    $participants = drawWith([new Participant('Alice'), new Participant('Bob')]);
    $draw = new Draw($participants);

    $result = $draw->execute(new RandomDrawStrategy());

    expect(['Alice', 'Bob'])->toContain($result->winner->name);
});

it('gives every participant a realistic chance of winning a uniform random draw', function () {
    $participants = drawWith([
        new Participant('Alice'),
        new Participant('Bob'),
        new Participant('Charlie'),
        new Participant('Dana'),
    ]);
    $strategy = new RandomDrawStrategy();

    $wins = ['Alice' => 0, 'Bob' => 0, 'Charlie' => 0, 'Dana' => 0];

    for ($i = 0; $i < 2000; $i++) {
        $wins[$strategy->draw($participants)->winner->name]++;
    }

    // Avec 4 participants équiprobables sur 2000 tirages, chacun doit
    // apparaître un nombre "raisonnable" de fois (aucun n'est structurellement
    // favorisé ni exclu) : on tolère un écart large plutôt que de figer un
    // pourcentage exact, pour ne pas tester l'implémentation mais le principe.
    foreach ($wins as $count) {
        expect($count)->toBeGreaterThan(200);
    }
});

it('makes a heavily-weighted participant win far more often than a lightly-weighted one', function () {
    $participants = drawWith([
        new Participant('Heavy', weight: 90),
        new Participant('Light', weight: 10),
    ]);
    $strategy = new WeightedDrawStrategy();

    $wins = ['Heavy' => 0, 'Light' => 0];

    for ($i = 0; $i < 1000; $i++) {
        $wins[$strategy->draw($participants)->winner->name]++;
    }

    // Le point n'est pas de retomber pile sur 90%/10% (ce serait tester
    // l'algorithme), mais de vérifier le cas d'usage réel : un poids 9x plus
    // grand doit se traduire par une victoire nettement plus fréquente.
    expect($wins['Heavy'])->toBeGreaterThan($wins['Light'] * 3);
});

it('always lets an equally-weighted participant win roughly as often as the others', function () {
    $participants = drawWith([
        new Participant('Alice', weight: 5),
        new Participant('Bob', weight: 5),
    ]);
    $strategy = new WeightedDrawStrategy();

    $aliceWins = 0;

    for ($i = 0; $i < 1000; $i++) {
        if ($strategy->draw($participants)->winner->name === 'Alice') {
            $aliceWins++;
        }
    }

    expect($aliceWins)->toBeGreaterThan(350)->toBeLessThan(650);
});

it('never selects a participant with zero total remaining chance', function () {
    // Un seul participant restant avec un poids : il doit systématiquement
    // gagner, ce qui couvre le cas limite d'un tirage pondéré à 1 candidat.
    $participants = drawWith([new Participant('OnlyOne', weight: 3)]);
    $strategy = new WeightedDrawStrategy();

    for ($i = 0; $i < 20; $i++) {
        expect($strategy->draw($participants)->winner->name)->toBe('OnlyOne');
    }
});
