<?php

namespace App\Domain\Draw\Contracts;

use App\Domain\Draw\Collections\Participants;
use App\Domain\Draw\ValueObjects\DrawResult;

/**
 * Contrat (Interface) définissant l'algorithme de tirage (Pattern Strategy).
 * Isole les règles de sélection du Domaine des couches d'application et de présentation.
 */
interface DrawStrategy
{
    /**
     * Exécute la logique algorithmique de sélection du ou des gagnants.
     *
     * Note technique : Impose un typage strict en entrée (Collection Participants)
     * et en sortie (Value Object DrawResult) pour toutes les implémentations concrètes.
     */
    public function draw(
        Participants $participants
    ): DrawResult;
}
