<?php

namespace App\Domain\CoinFlip\Contracts;

use App\Domain\CoinFlip\ValueObjects\CoinFlipResult;

/**
 * Contrat pour les algorithmes de tirage de pièce (Strategy Pattern).
 */
interface CoinFlipStrategy
{
    /**
     * Exécute le tirage et retourne la face gagnante.
     */
    public function flip(): CoinFlipResult;
}
