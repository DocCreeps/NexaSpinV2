<?php

namespace App\Domain\RockPaperScissors\Enums;

enum RpsOutcome: string
{
    case WIN = 'win';
    case LOSE = 'lose';
    case DRAW = 'draw';
}
