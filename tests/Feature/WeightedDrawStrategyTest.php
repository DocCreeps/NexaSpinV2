<?php

use App\Domain\Draw\Collections\Participants;
use App\Domain\Draw\Strategies\WeightedDrawStrategy;
use App\Domain\Draw\ValueObjects\DrawResult;
use App\Domain\Draw\ValueObjects\Participant;

it('always returns the sole heavily-weighted participant', function () {
    $strategy = new WeightedDrawStrategy;

    $participants = new Participants([
        new Participant('Jamais', weight: 1),
        new Participant('Toujours', weight: 999),
    ]);

    $result = $strategy->draw($participants);

    expect($result)->toBeInstanceOf(DrawResult::class)
        ->and($result->winner->name)->toBe('Toujours');
});

it('only returns a participant with weight zero-impossible, i.e. respects the given pool', function () {
    $strategy = new WeightedDrawStrategy;

    $participants = new Participants([
        new Participant('Alice', weight: 3),
        new Participant('Bob', weight: 7),
    ]);

    $result = $strategy->draw($participants);

    expect(['Alice', 'Bob'])->toContain($result->winner->name);
});

it('distributes wins roughly proportionally to weight over many draws', function () {
    $strategy = new WeightedDrawStrategy;

    $participants = new Participants([
        new Participant('Rare', weight: 1),
        new Participant('Frequent', weight: 9),
    ]);

    $wins = ['Rare' => 0, 'Frequent' => 0];

    for ($i = 0; $i < 500; $i++) {
        $wins[$strategy->draw($participants)->winner->name]++;
    }

    // Sur 500 tirages avec un ratio 1:9, "Frequent" doit très largement dominer.
    // Marge large pour éviter un test flaky (ce n'est pas une assertion statistique stricte).
    expect($wins['Frequent'])->toBeGreaterThan($wins['Rare'] * 3);
});
