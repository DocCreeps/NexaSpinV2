<?php

namespace App\Application\Draw\Actions;

use App\Application\Draw\DTOs\DrawData;
use App\Application\Draw\Resolvers\DrawStrategyResolver;
use App\Domain\Draw\ValueObjects\DrawResult;

/**
 * Action (Use Case) orchestrant le processus de tirage au sort.
 * Classe fermée à l'extension (final) possédant une unique responsabilité.
 */
final class RunDrawAction
{
    /**
     * Injection de dépendance via la promotion de propriété (PHP 8+).
     * Le mot-clé "readonly" garantit que l'instance de la Factory reste immuable.
     */
    public function __construct(
        private readonly DrawStrategyResolver $resolver
    ) {}

    /**
     * Point d'entrée de l'action.
     * Coordonne la résolution de la stratégie et délègue l'exécution au Domaine.
     */
    public function execute(DrawData $data): DrawResult
    {
        // Résolution dynamique de la stratégie (découplage de l'instanciation).
        $strategy = $this->resolver->resolve($data->type);

        // Passage de la collection typée issue du Domaine à la stratégie sélectionnée.
        return $strategy->draw(
            $data->participantsCollection()
        );
    }
}
