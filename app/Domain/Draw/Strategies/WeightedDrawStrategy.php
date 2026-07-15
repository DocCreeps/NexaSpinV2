<?php

namespace App\Domain\Draw\Strategies;

use App\Domain\Draw\Collections\Participants;
use App\Domain\Draw\Contracts\DrawStrategy;
use App\Domain\Draw\ValueObjects\DrawResult;
use App\Domain\Draw\ValueObjects\Participant;

/**
 * Implémentation d'un tirage aléatoire pondéré (probabilités proportionnelles au poids).
 *
 * Algorithme "Roulette Wheel Selection" : on tire un nombre aléatoire dans la plage
 * [1, somme des poids], puis on parcourt les participants en cumulant leur poids
 * jusqu'à dépasser ce nombre — le participant qui fait basculer le cumul est le gagnant.
 */
final class WeightedDrawStrategy implements DrawStrategy
{
    public function draw(
        Participants $participants
    ): DrawResult {
        $totalWeight = array_sum(
            array_map(
                fn (Participant $participant) => $participant->weight,
                $participants->all()
            )
        );

        $target = random_int(1, $totalWeight);

        $cumulative = 0;

        foreach ($participants as $participant) {
            $cumulative += $participant->weight;

            if ($target <= $cumulative) {
                return new DrawResult($participant);
            }
        }

        // Filet de sécurité, mathématiquement inatteignable si $totalWeight > 0
        // (garanti par Participant, dont le poids est toujours >= 1).
        return new DrawResult($participants->first());
    }
}
