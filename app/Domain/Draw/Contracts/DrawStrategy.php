<?php

namespace App\Domain\Draw\Contracts;

use App\Domain\Draw\ValueObjects\Participant;

interface DrawStrategy
{
    /**
     * @param  array<int, Participant>  $participants
     */
    public function draw(array $participants, array $options = []): ?Participant;
}
