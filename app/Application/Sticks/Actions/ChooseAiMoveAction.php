<?php

namespace App\Application\Sticks\Actions;

use App\Domain\Sticks\Contracts\SticksOpponentStrategy;
use App\Domain\Sticks\Entities\SticksGame;

/**
 * Traduit le choix de la stratégie (combien de bâtonnets) en un coup concret :
 * un bloc de bâtonnets voisins parmi ceux qui restent, à un endroit tiré au hasard.
 */
final class ChooseAiMoveAction
{
    public function __construct(
        private readonly SticksOpponentStrategy $strategy,
    ) {}

    /**
     * @return array<int, int> Positions des bâtonnets à retirer.
     */
    public function execute(SticksGame $game): array
    {
        $count = $this->strategy->choose($game);
        $remaining = $game->remainingSticks();

        return array_slice($remaining, random_int(0, count($remaining) - $count), $count);
    }
}
