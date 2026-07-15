<?php

namespace App\Application\Draw\DTOs;

use App\Domain\Draw\Collections\Participants;
use App\Domain\Draw\Enums\DrawDisplay;
use App\Domain\Draw\Enums\DrawType;
use App\Domain\Draw\ValueObjects\Participant;

/**
 * Data Transfer Object (DTO) immuable pour l'entrée des données.
 * Le combo "final readonly" verrouille l'état de l'objet dès sa création.
 */
final readonly class DrawData
{
    /**
     * @param  array<int,string>  $participants  Liste brute des noms.
     * @param  array<string,mixed>  $options  Clés/valeurs de configuration optionnelles.
     * @param  array<int,int>  $weights  Poids par index, parallèle à $participants.

     **/
    public function __construct(
        public array $participants,
        public DrawType $type,
        public DrawDisplay $display,
        public array $options = [],
        public array $weights = [],
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
                fn(string $name, int $index) => new Participant(
                    name: $name,
                    weight: $this->weights[$index] ?? 1,
                ),
                $this->participants,
                array_keys($this->participants)
            )
        );
    }
}
