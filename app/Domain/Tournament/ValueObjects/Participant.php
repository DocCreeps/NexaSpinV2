<?php

namespace App\Domain\Tournament\ValueObjects;

use InvalidArgumentException;

/**
 * Value Object immuable représentant un participant d'un tournoi (bracket ou
 * phase de poules). Partagé entre les sous-domaines Bracket et Pool, qui
 * s'appuient tous deux sur la même notion de participant ; isolé du
 * Participant du sous-domaine Draw pour éviter tout couplage inter-domaines.
 */
final readonly class Participant
{
    public string $name;

    public function __construct(string $name)
    {
        $this->name = trim($name);
        $this->validate();
    }

    private function validate(): void
    {
        if ($this->name === '') {
            throw new InvalidArgumentException('Participant name cannot be empty.');
        }
    }

    public function equals(self $other): bool
    {
        return $this->name === $other->name;
    }
}
