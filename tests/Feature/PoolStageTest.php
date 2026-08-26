<?php

use App\Domain\Tournament\Collections\Participants;
use App\Domain\Tournament\Pool\Entities\Pool;
use App\Domain\Tournament\Pool\Entities\PoolStage;
use App\Domain\Tournament\ValueObjects\Participant;

function makePoolParticipants(int $count): Participants
{
    return new Participants(
        array_map(fn (int $i) => new Participant("P{$i}"), range(1, $count))
    );
}

it('distributes participants into balanced pools with no empty match', function (int $count) {
    $stage = new PoolStage(makePoolParticipants($count));

    $sizes = array_map(fn ($pool) => $pool->size(), $stage->pools());

    expect(max($sizes) - min($sizes))->toBeLessThanOrEqual(1)
        ->and(array_sum($sizes))->toBe($count)
        ->and(min($sizes))->toBeGreaterThanOrEqual(3);

    foreach ($stage->pools() as $pool) {
        $expectedMatches = $pool->size() * ($pool->size() - 1) / 2;
        expect(count($pool->matches()))->toBe($expectedMatches);
    }
})->with([4, 5, 6, 9, 10, 11, 13, 17, 23]);

it('never schedules the same pair twice nor the same participant twice on the same matchday', function () {
    $stage = new PoolStage(makePoolParticipants(11));

    foreach ($stage->pools() as $pool) {
        $seenPairs = [];

        foreach ($pool->matches() as $match) {
            $key = collect([$match->participantA()->name, $match->participantB()->name])
                ->sort()
                ->implode('|');

            expect($seenPairs)->not->toHaveKey($key);
            $seenPairs[$key] = true;
        }

        foreach ($pool->matchdays() as $day) {
            $playersToday = [];
            foreach ($day as $match) {
                foreach ([$match->participantA()->name, $match->participantB()->name] as $name) {
                    expect($playersToday)->not->toHaveKey($name);
                    $playersToday[$name] = true;
                }
            }
        }
    }
});

it('computes standings ordered by win count once matches are resolved', function () {
    $stage = new PoolStage(makePoolParticipants(4));
    $pool = $stage->pools()[0];

    foreach ($pool->matches() as $match) {
        $match->recordResult($match->participantA());
    }

    expect($pool->isComplete())->toBeTrue()
        ->and($stage->isComplete())->toBeTrue();

    $standings = $pool->standings();
    expect($standings[0]['wins'])->toBeGreaterThanOrEqual($standings[1]['wins']);
});

it('automatically picks the pool count without any manual input', function () {
    // 4 à 6 participants : trop peu pour scinder sans repasser sous MIN_POOL_SIZE -> 1 poule.
    expect(count((new PoolStage(makePoolParticipants(4)))->pools()))->toBe(1)
        ->and(count((new PoolStage(makePoolParticipants(6)))->pools()))->toBe(2)
        // 8 participants : une poule de 8 serait trop grande, l'algo bascule sur 2 poules de 4.
        ->and(count((new PoolStage(makePoolParticipants(8)))->pools()))->toBe(2)
        // 16 participants : viser des poules de taille ~4 donne 4 poules.
        ->and(count((new PoolStage(makePoolParticipants(16)))->pools()))->toBe(4);
});

it('avoids a participant playing two consecutive matches in the flat match order when the pool is large enough', function () {
    // À partir de 5 participants dans une même poule, chaque journée compte
    // assez de matchs pour toujours trouver une paire "propre" à placer en
    // tête de la journée suivante. En dessous (poule de 3 ou 4), ce n'est pas
    // toujours possible structurellement, ce test porte donc sur des poules
    // directement instanciées à la taille voulue plutôt que sur PoolStage
    // (qui pourrait répartir en poules plus petites).
    foreach ([5, 6, 7, 8, 9, 10, 12] as $size) {
        $pool = new Pool('Test', makePoolParticipants($size)->all());
        $matches = $pool->matches();

        for ($i = 0; $i < count($matches) - 1; $i++) {
            $current = [$matches[$i]->participantA()->name, $matches[$i]->participantB()->name];
            $next = [$matches[$i + 1]->participantA()->name, $matches[$i + 1]->participantB()->name];

            expect(array_intersect($current, $next))->toBe([]);
        }
    }
});
