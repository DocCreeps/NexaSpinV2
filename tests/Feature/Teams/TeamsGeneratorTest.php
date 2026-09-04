<?php

use App\Application\Teams\TeamsGenerator;

it('splits participants evenly across teams when the count divides exactly', function () {
    $generator = new TeamsGenerator();

    $result = $generator->generate(['A', 'B', 'C', 'D', 'E', 'F'], teamsCount: 3);

    expect($result['teams'])->toHaveCount(3)
        ->and($result['substitutes'])->toBe([]);

    foreach ($result['teams'] as $team) {
        expect($team)->toHaveCount(2);
    }
});

it('sends the leftover participants to the substitutes bench', function () {
    $generator = new TeamsGenerator();

    // 7 participants across 3 teams: 1 per team = 6 placed, 1 leftover substitute.
    $result = $generator->generate(['A', 'B', 'C', 'D', 'E', 'F', 'G'], teamsCount: 3);

    expect($result['substitutes'])->toHaveCount(1);

    $placed = array_merge(...$result['teams']);

    expect($placed)->toHaveCount(6);
});

it('never loses or duplicates a participant across teams and substitutes', function () {
    $generator = new TeamsGenerator();
    $participants = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I'];

    $result = $generator->generate($participants, teamsCount: 4);

    $everyone = array_merge(array_merge(...$result['teams']), $result['substitutes']);
    sort($everyone);
    $expected = $participants;
    sort($expected);

    expect($everyone)->toBe($expected);
});

it('returns as many team slots as requested even if some end up empty-free of substitutes', function () {
    $generator = new TeamsGenerator();

    $result = $generator->generate(['A', 'B', 'C', 'D'], teamsCount: 4);

    expect($result['teams'])->toHaveCount(4)
        ->and($result['substitutes'])->toBe([]);

    foreach ($result['teams'] as $team) {
        expect($team)->toHaveCount(1);
    }
});

it('shuffles participants (does not always keep them in the same team across runs)', function () {
    $generator = new TeamsGenerator();
    $participants = array_map(fn (int $i) => "P{$i}", range(1, 20));

    $firstRun = $generator->generate($participants, teamsCount: 2)['teams'][0];
    $sameEveryTime = true;

    for ($i = 0; $i < 10; $i++) {
        $run = $generator->generate($participants, teamsCount: 2)['teams'][0];

        if ($run !== $firstRun) {
            $sameEveryTime = false;
            break;
        }
    }

    expect($sameEveryTime)->toBeFalse();
});
