<?php

namespace App\Application\Draw\Resolvers;

use App\Domain\Draw\Contracts\DrawStrategy;
use App\Domain\Draw\Enums\DrawType;
use App\Domain\Draw\Exceptions\DrawTypeNotSupportedException;
use App\Domain\Draw\Strategies\RandomDrawStrategy;

/**
 * Associe un type de tirage (DrawType) à sa stratégie concrète (Pattern Strategy).
 */
final class DrawStrategyResolver
{
    /**
     * Injection et verrouillage (readonly) des stratégies résolues par le conteneur IoC.
     */
    public function __construct(
        private readonly RandomDrawStrategy $random,
    ) {}

    /**
     * Retourne le contrat (Interface).
     * Utilisation d'un "match" pour une sélection stricte et exhaustive.
     */
    public function resolve(DrawType $type): DrawStrategy
    {
        return match ($type) {
            DrawType::RANDOM => $this->random,

            // Lève une exception de Domaine typée via un constructeur statique nommé.
            DrawType::WEIGHTED => throw DrawTypeNotSupportedException::forType($type),
        };
    }
}
