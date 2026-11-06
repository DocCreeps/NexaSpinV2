<?php

namespace App\Domain\Sticks\Enums;

enum SticksOpponentType: string
{
    case AI = 'ai';
    case LOCAL = 'local';

    public function label(): string
    {
        return match ($this) {
            self::AI => 'Contre l\'IA',
            self::LOCAL => 'Duel local',
        };
    }

    /** Libellé du joueur 1 (toujours l'humain, ou le premier joueur en duel local). */
    public function p1Label(): string
    {
        return match ($this) {
            self::AI => 'Vous',
            self::LOCAL => 'Joueur 1',
        };
    }

    /** Libellé du joueur 2 (l'IA, ou le second joueur en duel local). */
    public function p2Label(): string
    {
        return match ($this) {
            self::AI => 'IA',
            self::LOCAL => 'Joueur 2',
        };
    }
}
