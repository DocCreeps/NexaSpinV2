<?php

namespace App\Domain\Draw\Support;

/**
 * Tirage pondéré (algorithme "Roulette Wheel Selection") sur un simple tableau
 * de poids indexé, sans passer par l'entité `Draw` — celle-ci impose un
 * invariant d'au moins 2 participants, ce qui ne convient pas quand il ne
 * reste plus qu'un seul candidat (ex. dernier lot d'une tombola).
 *
 * Utilise `random_int()` (CSPRNG), comme `WeightedDrawStrategy`.
 */
final class WeightedRandomPicker
{
    /**
     * @param  array<int, int>  $weights  Poids indexés (les clés sont conservées dans le résultat).
     * @return int La clé du poids tiré.
     */
    public static function pick(array $weights): int
    {
        if ($weights === []) {
            throw new \InvalidArgumentException('Impossible de tirer un poids parmi un tableau vide.');
        }

        $totalWeight = array_sum($weights);

        if ($totalWeight <= 0) {
            return array_key_first($weights);
        }

        $target = random_int(1, $totalWeight);
        $cumulative = 0;

        foreach ($weights as $index => $weight) {
            $cumulative += $weight;

            if ($target <= $cumulative) {
                return $index;
            }
        }

        // Filet de sécurité, mathématiquement inatteignable si $totalWeight > 0.
        return array_key_first($weights);
    }
}
