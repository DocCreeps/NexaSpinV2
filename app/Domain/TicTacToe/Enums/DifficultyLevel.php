<?php

namespace App\Domain\TicTacToe\Enums;

enum DifficultyLevel: string
{
    case EASY = 'easy';
    case MEDIUM = 'medium';
    case HARD = 'hard';

    public function label(): string
    {
        return match ($this) {
            self::EASY => 'Facile',
            self::MEDIUM => 'Moyen',
            self::HARD => 'Imbattable',
        };
    }

    public function errorRate(): int
    {
        return match ($this) {
            self::EASY => 70,   // 70% de coups aléatoires
            self::MEDIUM => 30, // 30% d'erreurs
            self::HARD => 0,    // 0% d'erreurs (Minimax pur)
        };
    }
}
