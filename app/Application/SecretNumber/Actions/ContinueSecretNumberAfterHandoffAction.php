<?php

namespace App\Application\SecretNumber\Actions;

use App\Domain\SecretNumber\Entities\SecretNumberGame;

final class ContinueSecretNumberAfterHandoffAction
{
    public function execute(SecretNumberGame $game): SecretNumberGame
    {
        $game->continueAfterHandoff();

        return $game;
    }
}
