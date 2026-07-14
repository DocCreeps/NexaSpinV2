<?php

namespace App\Application\Draw\Factories;

use App\Domain\Draw\Contracts\DrawStrategy;
use App\Domain\Draw\Enums\DrawType;
use App\Domain\Draw\Strategies\RandomDrawStrategy;

/**
 * Factory chargée d'isoler l'instanciation des stratégies.
 * Centralise la logique de création pour éviter de disperser des "new" dans l'application.
 */
final class DrawFactory
{
    /**
     * Résout et instancie la stratégie correspondante.
     *
     * Note technique : En restant non-statique, la factory peut être injectée comme dépendance,
     * ce qui permet de la mocker facilement lors des tests unitaires.
     */
    public function make(DrawType $type): DrawStrategy
    {
        // Retourne le contrat (DrawStrategy) via une structure match exhaustive et stricte.
        return match ($type) {
            DrawType::RANDOM => new RandomDrawStrategy(),
        };
    }
}
