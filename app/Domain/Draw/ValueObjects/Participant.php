<?php

namespace App\Domain\Draw\ValueObjects;

class Participant
{
    public function __construct(
        public readonly string $name,
        public readonly int $weight = 1,
    ) {}
}
