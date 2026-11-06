<?php

namespace App\Application\Sticks\Actions;

use App\Domain\Sticks\Entities\SticksGame;
use App\Domain\Sticks\Enums\SticksVariant;

/**
 * Rejoue une liste de coups pour reconstruire une partie cohérente — même
 * principe que ReplayMovesAction (morpion) : seuls les coups sont persistés côté
 * Livewire (#[Locked]), la partie est recalculée à chaque accès via #[Computed].
 */
final class ReplaySticksMovesAction
{
    /**
     * @param  array<int, array<int, int>>  $moves  Bâtonnets retirés à chaque coup, dans l'ordre.
     */
    public function execute(int $stickCount, array $moves, int $firstPlayer = 1, SticksVariant $variant = SticksVariant::LAST_LOSES): SticksGame
    {
        $game = new SticksGame($stickCount, $firstPlayer, $variant);

        foreach ($moves as $indices) {
            $game->remove($indices);
        }

        return $game;
    }
}
