<?php

namespace App\Domain\Draw\Strategies;

use App\Domain\Draw\Contracts\DrawStrategy;
use App\Domain\Draw\Collections\Participants;
use App\Domain\Draw\ValueObjects\DrawResult;


final class WheelDrawStrategy implements DrawStrategy
{
    public function draw(
        Participants $participants
    ): DrawResult {

        return new DrawResult(
            $participants->random()
        );
    }
}
