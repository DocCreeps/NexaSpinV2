<?php

namespace App\Domain\SecretNumber\Enums;

/** Raison pour laquelle l'appareil change de mains pendant l'écran de passage. */
enum SecretNumberHandoffPurpose: string
{
    case GUESS = 'guess';
    case CHOOSE_SECRET = 'choose_secret';
}
