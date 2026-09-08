<?php

namespace App\Application\TicTacToe\Actions;

use App\Domain\TicTacToe\Entities\Board;
use App\Domain\TicTacToe\Enums\Mark;

/**
 * Rejoue une liste de coups pour reconstruire un Board cohérent — même
 * principe que RebuildPoolStageAction / RebuildDoubleEliminationBracketAction :
 * seule la liste des coups est persistée côté Livewire (#[Locked]), le Board
 * est recalculé à chaque accès via #[Computed].
 */
final class ReplayMovesAction
{
    /**
     * @param array<int, int> $moves Positions jouées, dans l'ordre.
     */
    public function execute(array $moves, Mark $firstPlayer = Mark::X): Board
    {
        $board = new Board($firstPlayer);

        foreach ($moves as $position) {
            $board->play($position);
        }

        return $board;
    }
}
