<?php

namespace App\Domain\CoinFlip\Strategies;

use App\Domain\CoinFlip\Contracts\CoinFlipStrategy;
use App\Domain\CoinFlip\Enums\CoinSide;
use App\Domain\CoinFlip\ValueObjects\CoinFlipResult;

/**
 * Implémentation d'un tirage équiprobable (50/50).
 */
final class RandomCoinFlipStrategy implements CoinFlipStrategy
{
    /**
     * Tire une face au hasard de façon uniforme et sécurisée.
     */
    public function flip(): CoinFlipResult
    {
        $side = random_int(0, 1) === 0
            ? CoinSide::PILE
            : CoinSide::FACE;

        return new CoinFlipResult($side);
    }
}
