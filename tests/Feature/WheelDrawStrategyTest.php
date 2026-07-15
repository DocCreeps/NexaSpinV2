<?php

use App\Domain\Draw\Collections\Participants;
use App\Domain\Draw\Exceptions\InvalidDrawException;
use App\Domain\Draw\Strategies\WheelDrawStrategy;
use App\Domain\Draw\ValueObjects\DrawResult;
use App\Domain\Draw\ValueObjects\Participant;

it('returns a winner among the given participants', function () {
    $names = ['John', 'Jane', 'Bob'];
    $participants = new Participants(array_map(fn (string $n) => new Participant($n), $names));

    $result = (new WheelDrawStrategy)->draw($participants);

    expect($result)->toBeInstanceOf(DrawResult::class)
        ->and($names)->toContain($result->winner->name);
});

it('throws when drawing from an empty collection', function () {
    (new WheelDrawStrategy)->draw(new Participants([]));
})->throws(InvalidDrawException::class);

// NB (limitation connue, voir README) : à ce jour WheelDrawStrategy est une
// copie conforme de RandomDrawStrategy ($participants->random()) — pas de
// logique de tirage propre à la roue. Pas de test de "différence" ici
// puisqu'il n'y a rien à différencier tant qu'aucune vraie logique n'existe ;
// ce fichier sera à enrichir une fois la stratégie réellement implémentée.
