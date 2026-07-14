<?php

namespace App\Domain\Draw\ValueObjects;

/**
 * Représente le résultat d'un tirage.
 *
 * Cet objet encapsule la sortie du domaine
 * afin d'éviter de retourner directement
 * un participant.
 */
final readonly class DrawResult
{
    public function __construct(
        public Participant $winner,
    ) {}
}
