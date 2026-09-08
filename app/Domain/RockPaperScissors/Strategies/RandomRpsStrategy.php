<?php

namespace App\Domain\RockPaperScissors\Strategies;

use App\Domain\RockPaperScissors\Contracts\RpsOpponentStrategy;
use App\Domain\RockPaperScissors\Enums\RpsChoice;

final class RandomRpsStrategy implements RpsOpponentStrategy
{
    public function choose(): RpsChoice
    {
        $cases = RpsChoice::cases();

        return $cases[random_int(0, count($cases) - 1)];
    }
}
