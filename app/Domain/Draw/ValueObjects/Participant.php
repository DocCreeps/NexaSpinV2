<?php

namespace App\Domain\Draw\ValueObjects;

use InvalidArgumentException;

/**
 * Value Object immuable représentant un participant.
 * Encapsule son identité et sa pondération (poids) pour les tirages au sort.
 */
final readonly class Participant
{
    public string $name;

    /**
     * @param  string  $name  Nom du participant (nettoyé des espaces superflus).
     * @param  int  $weight  Poids d'influence pour les tirages pondérés (par défaut 1).
     */
    public function __construct(
        string $name,
        public int $weight = 1,
    ) {
        // Nettoyage effectif des espaces superflus, conformément à la documentation
        // du paramètre : on ne veut pas se contenter de valider, il faut aussi stocker
        // la version nettoyée pour que tout consommateur du VO obtienne un nom cohérent.
        $this->name = trim($name);

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
        if ($this->name === '') {
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
