<?php

namespace App\Domain\CoinFlip\ValueObjects;

use App\Domain\CoinFlip\Enums\CoinSide;

/**
 * Représente le résultat immuable d'un tirage.
 */
final readonly class CoinFlipResult
{
    public function __construct(
        public CoinSide $side,
    ) {}
}
