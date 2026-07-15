<?php

namespace App\Domain\Draw\Strategies;

use App\Domain\Draw\Collections\Participants;
use App\Domain\Draw\Contracts\DrawStrategy;
use App\Domain\Draw\ValueObjects\DrawResult;

/**
 * Implémentation concrète d'un tirage aléatoire uniforme (sans pondération).
 * Classe immuable et fermée (final) répondant au contrat DrawStrategy.
 */
final class RandomDrawStrategy implements DrawStrategy
{
    /**
     * Sélectionne un gagnant au hasard et encapsule le résultat.
     *
     * Note technique : Délègue la sélection aléatoire directement à la collection
     * de participants pour respecter l'encapsulation et éviter de manipuler le tableau brut ici.
     */
    public function draw(
        Participants $participants
    ): DrawResult {
        return new DrawResult(
            $participants->random()
        );
    }
}
