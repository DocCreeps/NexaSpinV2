<?php

namespace App\Domain\RockPaperScissors\Contracts;

use App\Domain\RockPaperScissors\Enums\RpsChoice;

interface RpsOpponentStrategy
{
    public function choose(): RpsChoice;
}
