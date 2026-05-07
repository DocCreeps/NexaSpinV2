<?php

namespace App\Domain\Draw\Engines;

use App\Domain\Draw\Contracts\DrawStrategy;

class RandomDraw implements DrawStrategy
{
    public function draw(array $participants, array $options = []): mixed
    {
        if (empty($participants)) {
            return null;
        }

        return $participants[array_rand($participants)];
    }
}
