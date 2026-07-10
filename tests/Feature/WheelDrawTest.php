<?php

use App\Domain\Draw\Engines\WheelDraw;
use App\Domain\Draw\ValueObjects\Participant;

it('returns a participant among the given ones', function () {

    $engine = new WheelDraw();

    $participants = [
        new Participant('John'),
        new Participant('Jane'),
        new Participant('Bob'),
    ];

    $result = $engine->draw($participants);

    expect($result)->toBeInstanceOf(Participant::class)
        ->and(collect($participants)->pluck('name'))->toContain($result->name);
});

it('returns null when there are no participants', function () {

    $engine = new WheelDraw();

    expect($engine->draw([]))->toBeNull();
});
