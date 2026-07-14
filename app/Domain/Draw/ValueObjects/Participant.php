<?php

namespace App\Domain\Draw\ValueObjects;

use InvalidArgumentException;

/**
 * Value Object immuable représentant un participant.
 * Encapsule son identité et sa pondération (poids) pour les tirages au sort.
 */
final readonly class Participant
{
    /**
     * @param string $name Nom du participant (sera nettoyé des espaces superflus).
     * @param int $weight Poids d'influence pour les tirages pondérés (par défaut 1).
     */
    public function __construct(
        public string $name,
        public int $weight = 1,
    ) {
        // Approche "Fail-Fast" : validation des invariants dès la construction de l'objet.
        $this->validate();
    }

    /**
     * Valide le respect des règles métier élémentaires du participant.
     *
     * @throws InvalidArgumentException Si le nom est vide ou si le poids est invalide.
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
