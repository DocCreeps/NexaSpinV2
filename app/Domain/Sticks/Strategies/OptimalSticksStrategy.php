<?php

namespace App\Domain\Sticks\Strategies;

use App\Domain\Sticks\Contracts\SticksOpponentStrategy;
use App\Domain\Sticks\Entities\SticksGame;
use App\Domain\Sticks\Enums\SticksVariant;
use LogicException;

/**
 * Adversaire du jeu des bâtonnets.
 *
 * Stratégie gagnante : laisser à l'adversaire un nombre de bâtonnets congru à 1
 * modulo 4 (…, 13, 9, 5, 1). Depuis n'importe quelle autre position, on y arrive
 * en retirant (restants - 1) mod 4 bâtonnets. Depuis une position perdue (déjà
 * congrue à 1 modulo 4), on en retire un seul pour prolonger la partie et
 * laisser à l'adversaire un maximum d'occasions de se tromper.
 *
 * Variante « le dernier gagne » : il faut au contraire laisser un multiple de 4
 * (…, 12, 8, 4, 0), soit retirer (restants mod 4) bâtonnets.
 */
final class OptimalSticksStrategy implements SticksOpponentStrategy
{
    public function __construct(
        private readonly int $errorRatePercent = 0,
    ) {}

    public function choose(SticksGame $game): int
    {
        if ($game->isOver()) {
            throw new LogicException('Aucun bâtonnet à retirer.');
        }

        $remaining = $game->remaining();

        // Erreur volontaire : un nombre de bâtonnets légal tiré au hasard.
        if (random_int(1, 100) <= $this->errorRatePercent) {
            return random_int(SticksGame::MIN_TAKE, min(SticksGame::MAX_TAKE, $remaining));
        }

        $modulo = SticksGame::MAX_TAKE + 1;
        $take = $game->variant === SticksVariant::LAST_WINS
            ? $remaining % $modulo
            : ($remaining - 1) % $modulo;

        return $take === 0 ? SticksGame::MIN_TAKE : $take;
    }
}
