<?php

namespace App\Application\Draw\Factories;

use App\Domain\Draw\Contracts\DrawStrategy;
use App\Domain\Draw\Engines\RandomDraw;
use App\Domain\Draw\Engines\WheelDraw;
use App\Domain\Draw\Enums\DrawType;
use App\Domain\Draw\Exceptions\DrawTypeNotSupportedException;

class DrawFactory
{
    public static function make(DrawType $type): DrawStrategy
    {
        return match ($type) {
            DrawType::RANDOM => new RandomDraw(),
            DrawType::WHEEL => new WheelDraw(),

            // WEIGHTED n'est pas encore implémenté : on lève une exception
            // explicite plutôt que de retomber silencieusement sur un autre
            // moteur, ce qui donnerait un résultat trompeur à l'utilisateur.
            DrawType::WEIGHTED => throw DrawTypeNotSupportedException::forType($type),
        };
    }
}
