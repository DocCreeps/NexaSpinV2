<?php

namespace App\Application\Tombola\DTOs;

/**
 * Résultat d'un tirage de lot unique : le gagnant, et le pool/poids restants
 * une fois ce gagnant retiré (ou son poids décrémenté, en mode avec remise).
 */
final class LotDrawResult
{
    /**
     * @param  array<int, string>  $remainingPool
     * @param  array<int, int>  $remainingWeights
     */
    public function __construct(
        public readonly string $winner,
        public readonly array $remainingPool,
        public readonly array $remainingWeights,
    ) {}
}
