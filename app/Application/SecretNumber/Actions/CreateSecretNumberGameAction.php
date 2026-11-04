<?php

namespace App\Application\SecretNumber\Actions;

use App\Domain\SecretNumber\Entities\SecretNumberGame;
use App\Domain\SecretNumber\Enums\SecretNumberMode;
use App\Domain\SecretNumber\ValueObjects\SecretNumberPlayers;
use App\Domain\SecretNumber\ValueObjects\SecretNumberRange;

final class CreateSecretNumberGameAction
{
    public function execute(SecretNumberMode $mode, SecretNumberRange $range, int $maxAttempts, SecretNumberPlayers $players): SecretNumberGame
    {
        if ($mode === SecretNumberMode::VERSUS) {
            return SecretNumberGame::forVersus($range, $maxAttempts, $players);
        }

        return SecretNumberGame::forAi($mode, $range, $maxAttempts, random_int($range->minimum, $range->maximum), $players);
    }
}
