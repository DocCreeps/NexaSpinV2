<?php

namespace App\Application\SecretNumber\Actions;

use App\Domain\SecretNumber\Entities\SecretNumberGame;

final class SubmitSecretNumberGuessAction
{
    public function execute(SecretNumberGame $game, int $guess): SecretNumberGame
    {
        $game->guess($guess);

        return $game;
    }
}
