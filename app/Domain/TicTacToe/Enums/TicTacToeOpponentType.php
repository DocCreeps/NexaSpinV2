<?php

namespace App\Domain\TicTacToe\Enums;

enum TicTacToeOpponentType: string
{
    case AI = 'ai';
    case LOCAL = 'local';

    public function label(): string
    {
        return match ($this) {
            self::AI => 'Contre l\'IA',
            self::LOCAL => 'Adversaire local',
        };
    }

    /** Libellé du joueur qui a le symbole X (celui qui commence toujours). */
    public function xLabel(): string
    {
        return match ($this) {
            self::AI => 'Vous',
            self::LOCAL => 'Joueur 1',
        };
    }

    /** Libellé du joueur (ou de l'IA) qui a le symbole O. */
    public function oLabel(): string
    {
        return match ($this) {
            self::AI => 'IA',
            self::LOCAL => 'Joueur 2',
        };
    }
}
