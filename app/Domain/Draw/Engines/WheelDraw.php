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

        return $participants[array_rand($participants)];
    }
}
