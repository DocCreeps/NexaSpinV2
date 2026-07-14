<?php

namespace App\Application\Draw\DTOs;

use App\Domain\Draw\Enums\DrawType;
use App\Domain\Draw\Enums\DrawDisplay;
use App\Domain\Draw\Collections\Participants;
use App\Domain\Draw\ValueObjects\Participant;

/**
 * Data Transfer Object (DTO) immuable pour l'entrée des données.
 * Le combo "final readonly" verrouille l'état de l'objet dès sa création.
 */
final readonly class DrawData
{
    /**
     * @param array<int,string> $participants Liste brute typée pour l'analyse statique (PHPStan).
     * @param array<string,mixed> $options Clés/valeurs de configuration optionnelles.
     */
    public function __construct(
        public array $participants,
        public DrawType $type,
        public DrawDisplay $display,
        public array $options = [],
    ) {}

    /**
     * Convertit les données d'entrée (scalaires) en objets du Domaine.
     *
     * Note technique : Encapsule l'instanciation de la collection "Participants"
     * et des Value Objects "Participant" pour isoler l'Action de cette plomberie.
     */
    public function participantsCollection(): Participants
    {
        return new Participants(
            array_map(
                fn(string $name) => new Participant($name),
                $this->participants
            )
        );
    }
}
