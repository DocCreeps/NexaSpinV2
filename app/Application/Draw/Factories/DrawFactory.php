<?php

namespace App\Application\Draw\Factories;


use App\Domain\Draw\Contracts\DrawStrategy;

use App\Domain\Draw\Enums\DrawType;

use App\Domain\Draw\Strategies\RandomDrawStrategy;


final class DrawFactory
{

    public static function make(
        DrawType $type
    ): DrawStrategy {

        return match ($type) {

            DrawType::RANDOM =>
            new RandomDrawStrategy(),
        };
    }
}
