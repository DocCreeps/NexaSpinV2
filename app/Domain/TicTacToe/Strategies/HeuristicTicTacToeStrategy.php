<?php

namespace App\Domain\TicTacToe\Strategies;

use App\Domain\TicTacToe\Contracts\TicTacToeOpponentStrategy;
use App\Domain\TicTacToe\Entities\Board;
use App\Domain\TicTacToe\Enums\Mark;

final class HeuristicTicTacToeStrategy implements TicTacToeOpponentStrategy
{
    public function __construct(
        private readonly int $errorRatePercent = 30
    ) {}

    public function choose(Board $board, Mark $aiMark): int
    {
        $emptyPositions = $board->emptyPositions();

        if (empty($emptyPositions)) {
            throw new \LogicException('Aucune case disponible.');
        }

        // Si le tirage aléatoire est inférieur au taux d'erreur, l'IA fait une erreur volontaire
        if (random_int(1, 100) <= $this->errorRatePercent) {
            return $emptyPositions[array_rand($emptyPositions)];
        }

        // Sinon, elle joue le meilleur coup (Minimax)
        return $this->findBestMove($board, $aiMark);
    }

    private function findBestMove(Board $board, Mark $aiMark): int
    {
        $bestScore = -INF;
        $bestMove = $board->emptyPositions()[0];

        foreach ($board->emptyPositions() as $position) {
            $simulatedBoard = clone $board;
            $simulatedBoard->play($position);

            $score = $this->minimax($simulatedBoard, 0, false, $aiMark);

            if ($score > $bestScore) {
                $bestScore = $score;
                $bestMove = $position;
            }
        }

        return $bestMove;
    }

    private function minimax(Board $board, int $depth, bool $isMaximizing, Mark $aiMark): int
    {
        if ($board->isOver()) {
            if ($board->winner() === $aiMark) {
                return 10 - $depth;
            }
            if ($board->winner() !== null) {
                return $depth - 10;
            }
            return 0;
        }

        if ($isMaximizing) {
            $bestScore = -INF;
            foreach ($board->emptyPositions() as $position) {
                $simulatedBoard = clone $board;
                $simulatedBoard->play($position);
                $score = $this->minimax($simulatedBoard, $depth + 1, false, $aiMark);
                $bestScore = max($score, $bestScore);
            }
            return (int) $bestScore;
        }

        $bestScore = INF;
        foreach ($board->emptyPositions() as $position) {
            $simulatedBoard = clone $board;
            $simulatedBoard->play($position);
            $score = $this->minimax($simulatedBoard, $depth + 1, true, $aiMark);
            $bestScore = min($score, $bestScore);
        }
        return (int) $bestScore;
    }
}
