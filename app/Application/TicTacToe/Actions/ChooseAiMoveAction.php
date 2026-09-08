<?php

namespace App\Application\TicTacToe\Actions;

use App\Domain\TicTacToe\Contracts\TicTacToeOpponentStrategy;
use App\Domain\TicTacToe\Entities\Board;

final class ChooseAiMoveAction
{
    public function __construct(
        private readonly TicTacToeOpponentStrategy $strategy,
    ) {}

    public function execute(Board $board): int
    {
        return $this->strategy->choose($board, $board->currentTurn());
    }
}
