<?php

namespace App\Domain\Draw\Strategies;

use App\Domain\Draw\Contracts\DrawStrategy;
use App\Domain\Draw\Collections\Participants;
use App\Domain\Draw\ValueObjects\DrawResult;

/**
 * Stratégie de tirage pour l'affichage sous forme de roue.
 * Partage la même logique mathématique uniforme que la stratégie aléatoire classique.
 */
final class WheelDrawStrategy implements DrawStrategy
{
    /**
     * Sélectionne le gagnant de manière aléatoire.
     *
     * Note technique : L'aspect visuel (animation de rotation, segments SVG) est géré
     * par la présentation (UI) et le Support, gardant ce service de domaine purement décisionnel.
     */
    public function draw(
        Participants $participants
    ): DrawResult {

        return new DrawResult(
            $participants->random()
        );
    }
}
