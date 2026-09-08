<?php

namespace App\Domain\RockPaperScissors\Enums;

enum RpsOpponentType: string
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

    /** Libellé du premier joueur (celui qui joue depuis cet appareil). */
    public function playerALabel(): string
    {
        return match ($this) {
            self::AI => 'Vous',
            self::LOCAL => 'Joueur 1',
        };
    }

    /** Libellé du second joueur (IA, ou second joueur local). */
    public function playerBLabel(): string
    {
        return match ($this) {
            self::AI => 'IA',
            self::LOCAL => 'Joueur 2',
        };
    }
}
