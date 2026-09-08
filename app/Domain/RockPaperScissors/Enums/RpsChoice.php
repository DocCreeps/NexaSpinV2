<?php

namespace App\Domain\RockPaperScissors\Enums;

enum RpsChoice: string
{
    case ROCK = 'rock';
    case PAPER = 'paper';
    case SCISSORS = 'scissors';

    public function label(): string
    {
        return match ($this) {
            self::ROCK => 'Pierre',
            self::PAPER => 'Feuille',
            self::SCISSORS => 'Ciseaux',
        };
    }

    /** Indique si ce choix bat l'autre choix passé en argument. */
    public function beats(self $other): bool
    {
        return match ($this) {
            self::ROCK => $other === self::SCISSORS,
            self::PAPER => $other === self::ROCK,
            self::SCISSORS => $other === self::PAPER,
        };
    }
}
