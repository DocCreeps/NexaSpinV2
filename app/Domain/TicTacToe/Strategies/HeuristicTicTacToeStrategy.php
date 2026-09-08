<?php

namespace App\Domain\TicTacToe\Strategies;

use App\Domain\TicTacToe\Contracts\TicTacToeOpponentStrategy;
use App\Domain\TicTacToe\Entities\Board;
use App\Domain\TicTacToe\Enums\Mark;

/**
 * IA simple à priorités : gagner si possible, sinon bloquer l'adversaire,
 * sinon prendre le centre, sinon un coin, sinon une case au hasard. Pas
 * imbattable (pas de minimax) mais évite les fautes évidentes.
 */
final class HeuristicTicTacToeStrategy implements TicTacToeOpponentStrategy
{
    private const CENTER = 4;

    private const CORNERS = [0, 2, 6, 8];

    public function choose(Board $board, Mark $mark): int
    {
        $empty = $board->emptyPositions();

        foreach ($empty as $position) {
            if ($board->wouldWin($position, $mark)) {
                return $position;
            }
        }

        foreach ($empty as $position) {
            if ($board->wouldWin($position, $mark->opponent())) {
                return $position;
            }
        }

        if (in_array(self::CENTER, $empty, true)) {
            return self::CENTER;
        }

        $availableCorners = array_values(array_intersect(self::CORNERS, $empty));

        if ($availableCorners !== []) {
            return $availableCorners[array_rand($availableCorners)];
        }

        return $empty[array_rand($empty)];
    }
}
