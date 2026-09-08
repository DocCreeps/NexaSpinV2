<?php

namespace App\Application\RockPaperScissors\Actions;

use App\Domain\RockPaperScissors\Contracts\RpsOpponentStrategy;
use App\Domain\RockPaperScissors\Enums\RpsChoice;
use App\Domain\RockPaperScissors\ValueObjects\RpsResult;

final class PlayRpsRoundAction
{
    public function __construct(
        private readonly RpsOpponentStrategy $strategy,
    ) {}

    public function execute(RpsChoice $playerChoice): RpsResult
    {
        return new RpsResult($playerChoice, $this->strategy->choose());
    }
}
