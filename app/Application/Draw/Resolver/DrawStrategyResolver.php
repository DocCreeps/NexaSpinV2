<?php

namespace App\Application\Draw\Resolvers;

use App\Domain\Draw\Contracts\DrawStrategy;
use App\Domain\Draw\Enums\DrawType;
use App\Domain\Draw\Exceptions\DrawTypeNotSupportedException;
use App\Domain\Draw\Strategies\RandomDrawStrategy;
use App\Domain\Draw\Strategies\WheelDrawStrategy;


/**
 * Résout la stratégie de tirage à utiliser.
 *
 * Cette classe fait le lien entre le choix utilisateur
 * (DrawType) et l'implémentation métier correspondante.
 */
final class DrawStrategyResolver
{
    public function __construct(
        private RandomDrawStrategy $random,
        private WheelDrawStrategy $wheel,
    ) {
    }


    public function resolve(
        DrawType $type
    ): DrawStrategy {

        return match ($type) {

            DrawType::RANDOM =>
                $this->random,

            DrawType::WHEEL =>
                $this->wheel,

            DrawType::WEIGHTED =>
                throw DrawTypeNotSupportedException::forType($type),
        };
    }
}
