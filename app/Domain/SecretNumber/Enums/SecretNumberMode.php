<?php

namespace App\Domain\SecretNumber\Enums;

enum SecretNumberMode: string
{
    case SOLO = 'solo';
    case DUEL = 'duel';
    case VERSUS = 'versus';

    public function label(): string
    {
        return match ($this) {
            self::SOLO => 'Solo contre l’IA',
            self::DUEL => 'Duel',
            self::VERSUS => 'Versus',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::SOLO => 'Trouvez le nombre choisi par l’IA.',
            self::DUEL => 'Même nombre pour tous. Chacun joue son tour ; le moins d’essais gagne.',
            self::VERSUS => 'Chacun choisit un nombre à faire deviner à l’autre. Le plus rapide gagne.',
        };
    }
}
