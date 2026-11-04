<?php

namespace App\Application\SecretNumber\Actions;

use App\Domain\SecretNumber\Entities\SecretNumberGame;

final class ChooseSecretNumberAction
{
    public function execute(SecretNumberGame $game, int $secret): SecretNumberGame
    {
        $game->chooseSecret($secret);

        return $game;
    }
}
