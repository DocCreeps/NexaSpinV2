<?php

namespace App\Application\Draw\DTOs;

use App\Domain\Draw\Enums\DrawType;
use App\Domain\Draw\ValueObjects\Participant;

class DrawData
{
    /**
     * @param  array<int, Participant>  $participants
     */
    public function __construct(
        public readonly array $participants,
        public readonly DrawType $type,
        public readonly array $options = [],
    ) {}
}
