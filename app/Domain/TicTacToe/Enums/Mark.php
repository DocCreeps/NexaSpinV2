<?php

namespace App\Domain\TicTacToe\Enums;

enum Mark: string
{
    case X = 'x';
    case O = 'o';

    public function opponent(): self
    {
        return $this === self::X ? self::O : self::X;
    }
}
