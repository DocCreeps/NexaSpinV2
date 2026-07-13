<?php

namespace App\Domain\Draw\Enums;

enum DrawType: string
{
    case RANDOM = 'random';

    case WHEEL = 'wheel';

    case WEIGHTED = 'weighted';


    public function label(): string
    {
        return match ($this) {

            self::RANDOM =>
            'Aléatoire',

            self::WHEEL =>
            'Roue',

            self::WEIGHTED =>
            'Pondéré',
        };
    }
}
