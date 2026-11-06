<?php

namespace App\Domain\Sticks\Enums;

enum SticksDifficulty: string
{
    case EASY = 'easy';
    case MEDIUM = 'medium';
    case HARD = 'hard';

    public function label(): string
    {
        return match ($this) {
            self::EASY => 'Facile',
            self::MEDIUM => 'Moyen',
            self::HARD => 'Expert',
        };
    }

    /** Pourcentage de coups aléatoires joués à la place du coup optimal. */
    public function errorRate(): int
    {
        return match ($this) {
            self::EASY => 70,
            self::MEDIUM => 30,
            self::HARD => 0,
        };
    }
}
