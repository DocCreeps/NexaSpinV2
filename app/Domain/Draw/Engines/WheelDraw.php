<?php

namespace App\Domain\Draw\Engines;

use App\Domain\Draw\Contracts\DrawStrategy;
use App\Domain\Draw\ValueObjects\Participant;

class WheelDraw implements DrawStrategy
{
    /**
     * @param  array<int, Participant>  $participants
     */
    public function draw(array $participants, array $options = []): ?Participant
    {
        if (empty($participants)) {
            return null;
        }

        // Le hasard est identique à RandomDraw : seule la présentation
        // (roue tournante) diffère côté interface. Si un jour la roue doit
        // avoir un biais différent, c'est ici qu'il faudra le faire.
        return $participants[array_rand($participants)];
    }
}
