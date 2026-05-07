<?php

use App\Domain\Draw\Engines\RandomDraw;

it('returns a participant', function () {

    $engine = new RandomDraw();

    $participants = [
        'John',
        'Jane',
        'Bob',
    ];

    $result = $engine->draw($participants);

    expect($participants)->toContain($result);
});
