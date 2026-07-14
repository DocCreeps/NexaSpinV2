<?php

namespace App\Domain\Draw\Enums;

/**
 * Définit uniquement la manière
 * dont le résultat est présenté.
 */
enum DrawDisplay: string
{
    case SIMPLE = 'simple';

    case WHEEL = 'wheel';


    public function label(): string
    {
        return match ($this) {

            self::SIMPLE =>
            'Simple',

            self::WHEEL =>
            'Roue',
        };
    }
}
