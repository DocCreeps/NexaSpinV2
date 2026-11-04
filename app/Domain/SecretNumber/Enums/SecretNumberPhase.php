<?php

namespace App\Domain\SecretNumber\Enums;

enum SecretNumberPhase: string
{
    /** Avant la création de la partie : jamais portée par l'entité, uniquement par la page. */
    case SETUP = 'setup';
    case CHOOSE_SECRET = 'choose_secret';
    case HANDOFF = 'handoff';
    case PLAYING = 'playing';
    case FINISHED = 'finished';
}
