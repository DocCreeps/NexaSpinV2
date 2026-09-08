<?php

namespace App\Domain\RockPaperScissors\ValueObjects;

use App\Domain\RockPaperScissors\Enums\RpsChoice;
use App\Domain\RockPaperScissors\Enums\RpsOutcome;

/**
 * Résultat immuable d'une manche. La règle métier (gagné/perdu/nul) est
 * calculée ici, dans le Domaine, comme CoinFlipBet::won() pour Pile ou Face.
 */
final readonly class RpsResult
{
    public RpsOutcome $outcome;

    public function __construct(
        public RpsChoice $player,
        public RpsChoice $opponent,
    ) {
        $this->outcome = match (true) {
            $this->player === $this->opponent => RpsOutcome::DRAW,
            $this->player->beats($this->opponent) => RpsOutcome::WIN,
            default => RpsOutcome::LOSE,
        };
    }
}
