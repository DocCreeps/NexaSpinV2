<?php

namespace App\Domain\Draw\Enums;

enum DrawType: string
{
    case RANDOM = 'random';

    case WEIGHTED = 'weighted';


    public function label(): string
    {
        return match ($this) {

            self::RANDOM =>
            'Aléatoire',

            self::WEIGHTED =>
            'Pondéré',
        };
    }
}
