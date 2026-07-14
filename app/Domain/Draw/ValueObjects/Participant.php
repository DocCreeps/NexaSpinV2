<?php

namespace App\Domain\Draw\ValueObjects;

use InvalidArgumentException;

/**
 * Représente un participant d'un tirage.
 *
 * Ce Value Object est immutable.
 */
final readonly class Participant
{
    public function __construct(
        public string $name,
        public int $weight = 1,
    ) {
        $this->validate();
    }

    /**
     * Vérifie que le participant respecte
     * les règles métier.
     */
    private function validate(): void
    {
        if (trim($this->name) === '') {
            throw new InvalidArgumentException(
                'Participant name cannot be empty.'
            );
        }

        if ($this->weight < 1) {
            throw new InvalidArgumentException(
                'Weight must be greater than zero.'
            );
        }
    }
}
