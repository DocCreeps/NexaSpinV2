<?php

namespace App\Domain\Draw\Contracts;

use App\Domain\Draw\Collections\Participants;
use App\Domain\Draw\ValueObjects\DrawResult;

/**
 * Contrat définissant la manière
 * dont un tirage sélectionne ses gagnants.
 *
 * Chaque stratégie applique sa propre règle
 * de sélection sans gérer la présentation
 * ni la persistance.
 */
interface DrawStrategy
{
    public function draw(
        Participants $participants
    ): DrawResult;
}
