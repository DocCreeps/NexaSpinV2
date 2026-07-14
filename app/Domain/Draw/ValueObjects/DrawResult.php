<?php

namespace App\Domain\Draw\ValueObjects;

/**
 * Value Object représentant le résultat immuable d'un tirage au sort.
 * Encapsule le gagnant sélectionné pour découpler la sortie du domaine et faciliter de futures extensions.
 */
final readonly class DrawResult
{
    public function __construct(
        public Participant $winner,
    ) {}
}
