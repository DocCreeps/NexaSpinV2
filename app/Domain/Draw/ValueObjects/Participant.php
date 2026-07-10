<?php

namespace App\Domain\Draw\ValueObjects;

use InvalidArgumentException;

class Participant
{
    public function __construct(
        public readonly string $name,
        public readonly int $weight = 1,
    ) {
        if (trim($this->name) === '') {
            throw new InvalidArgumentException('Le nom d\'un participant ne peut pas être vide.');
        }

        if ($this->weight < 1) {
            throw new InvalidArgumentException('Le poids d\'un participant doit être supérieur ou égal à 1.');
        }
    }
}
