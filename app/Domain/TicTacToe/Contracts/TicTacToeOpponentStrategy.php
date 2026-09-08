<?php

namespace App\Domain\TicTacToe\Contracts;

use App\Domain\TicTacToe\Entities\Board;
use App\Domain\TicTacToe\Enums\Mark;

interface TicTacToeOpponentStrategy
{
    /**
     * Retourne la position (0-8) que l'IA choisit de jouer sur le plateau donné.
     */
    public function choose(Board $board, Mark $mark): int;
}
