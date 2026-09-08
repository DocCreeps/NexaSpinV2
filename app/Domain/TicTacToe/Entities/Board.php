<?php

namespace App\Domain\TicTacToe\Entities;

use App\Domain\TicTacToe\Enums\Mark;
use App\Domain\TicTacToe\Exceptions\InvalidMoveException;

/**
 * Entité représentant l'état d'une grille de morpion (3x3, cases indexées
 * 0-8, de gauche à droite puis de haut en bas).
 */
final class Board
{
    private const WINNING_LINES = [
        [0, 1, 2], [3, 4, 5], [6, 7, 8], // lignes
        [0, 3, 6], [1, 4, 7], [2, 5, 8], // colonnes
        [0, 4, 8], [2, 4, 6],            // diagonales
    ];

    /** @var array<int, ?Mark> */
    private array $cells;

    private Mark $currentTurn;

    private ?Mark $winner = null;

    public function __construct(Mark $firstPlayer = Mark::X)
    {
        $this->cells = array_fill(0, 9, null);
        $this->currentTurn = $firstPlayer;
    }

    /**
     * @throws InvalidMoveException Si la partie est finie ou la case occupée.
     */
    public function play(int $position): void
    {
        if ($this->isOver()) {
            throw new InvalidMoveException('La partie est déjà terminée.');
        }

        if ($position < 0 || $position > 8) {
            throw new InvalidMoveException('Position invalide.');
        }

        if ($this->cells[$position] !== null) {
            throw new InvalidMoveException('Cette case est déjà occupée.');
        }

        $this->cells[$position] = $this->currentTurn;
        $this->winner = $this->detectWinner();
        $this->currentTurn = $this->currentTurn->opponent();
    }

    private function detectWinner(): ?Mark
    {
        foreach (self::WINNING_LINES as [$a, $b, $c]) {
            if ($this->cells[$a] !== null
                && $this->cells[$a] === $this->cells[$b]
                && $this->cells[$b] === $this->cells[$c]) {
                return $this->cells[$a];
            }
        }

        return null;
    }

    public function isDraw(): bool
    {
        return $this->winner === null && ! in_array(null, $this->cells, true);
    }

    public function isOver(): bool
    {
        return $this->winner !== null || $this->isDraw();
    }

    public function winner(): ?Mark
    {
        return $this->winner;
    }

    public function currentTurn(): Mark
    {
        return $this->currentTurn;
    }

    /** @return array<int, ?Mark> */
    public function cells(): array
    {
        return $this->cells;
    }

    /** @return array<int, int> Positions encore libres. */
    public function emptyPositions(): array
    {
        return array_keys(array_filter(
            $this->cells,
            fn (?Mark $mark) => $mark === null
        ));
    }

    /**
     * Indique si jouer $mark en $position ferait immédiatement gagner ce
     * mark (sans modifier l'état réel du plateau). Utilisé par l'IA pour
     * détecter les coups gagnants/bloquants sans avoir à jouer pour de vrai.
     */
    public function wouldWin(int $position, Mark $mark): bool
    {
        if ($position < 0 || $position > 8 || $this->cells[$position] !== null) {
            return false;
        }

        $cells = $this->cells;
        $cells[$position] = $mark;

        foreach (self::WINNING_LINES as [$a, $b, $c]) {
            if ($cells[$a] !== null && $cells[$a] === $cells[$b] && $cells[$b] === $cells[$c]) {
                return true;
            }
        }

        return false;
    }
}
