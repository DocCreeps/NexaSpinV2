<?php

namespace App\Application\Roulette;

use App\Domain\Roulette\Enums\RouletteBetType;
use App\Domain\Roulette\RoulettePocket;

/**
 * Détermine si une mise de roulette est gagnante pour le numéro tiré.
 */
final class RouletteBetEvaluator
{
    public function isWinning(RouletteBetType $betType, ?string $number, string $result): bool
    {
        return match ($betType) {
            RouletteBetType::STRAIGHT => $number !== null && $number === $result,
            RouletteBetType::RED => RoulettePocket::color($result) === 'red',
            RouletteBetType::BLACK => RoulettePocket::color($result) === 'black',
            RouletteBetType::EVEN => RoulettePocket::isEven($result),
            RouletteBetType::ODD => ! RoulettePocket::isZero($result) && ! RoulettePocket::isEven($result),
            RouletteBetType::LOW => ! RoulettePocket::isZero($result) && (int) $result <= 18,
            RouletteBetType::HIGH => ! RoulettePocket::isZero($result) && (int) $result >= 19,
            RouletteBetType::DOZEN_1 => RoulettePocket::dozen($result) === 1,
            RouletteBetType::DOZEN_2 => RoulettePocket::dozen($result) === 2,
            RouletteBetType::DOZEN_3 => RoulettePocket::dozen($result) === 3,
            RouletteBetType::COLUMN_1 => RoulettePocket::column($result) === 1,
            RouletteBetType::COLUMN_2 => RoulettePocket::column($result) === 2,
            RouletteBetType::COLUMN_3 => RoulettePocket::column($result) === 3,
            RouletteBetType::TOP_LINE => in_array($result, ['0', '00', '1', '2', '3'], true),
        };
    }
}
