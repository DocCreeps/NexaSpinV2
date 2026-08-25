<?php

namespace App\Application\Tournament\DTOs;

use App\Domain\Tournament\Collections\Participants;
use App\Domain\Tournament\ValueObjects\Participant;

/**
 * Data Transfer Object (DTO) immuable pour l'entrée des données d'un
 * tournoi (bracket ou phase de poules). Convertit les noms bruts saisis
 * par l'UI en Value Objects du Domaine.
 *
 * Partagé entre les formats Bracket et Pool : les deux ne font que
 * transporter une liste de noms de participants, sans rien de
 * spécifique au format à ce stade de saisie.
 */
final readonly class ParticipantListData
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
