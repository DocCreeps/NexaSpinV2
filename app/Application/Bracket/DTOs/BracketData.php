<?php

namespace App\Application\Bracket\DTOs;

use App\Domain\Bracket\Collections\Participants;
use App\Domain\Bracket\ValueObjects\Participant;

/**
 * Data Transfer Object (DTO) immuable pour l'entrée des données du bracket.
 * Convertit les noms bruts saisis par l'UI en Value Objects du Domaine.
 */
final readonly class BracketData
{
    /**
     * @param array<int, string> $participants Liste brute des noms, ordre de saisie.
     */
    public function __construct(
        public array $participants,
    ) {}

    public function participantsCollection(): Participants
    {
        return new Participants(
            array_map(
                fn (string $name) => new Participant($name),
                $this->participants
            )
        );
    }
}
