<?php

namespace App\Application\Draw\DTOs;

use App\Domain\Draw\Enums\DrawType;

class DrawData
{
    public function __construct(
        public readonly array $participants,
        public readonly DrawType $type,
        public readonly array $options = [],
    ) {}
}
