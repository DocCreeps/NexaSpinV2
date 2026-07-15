<?php

namespace App\Application\Draw\Actions;

use App\Application\Draw\DTOs\DrawData;
use App\Application\Draw\Resolvers\DrawStrategyResolver;
use App\Domain\Draw\Entities\Draw;
use App\Domain\Draw\ValueObjects\DrawResult;

/**
 * Action (Use Case) orchestrant le processus de tirage au sort.
 * Classe fermée à l'extension (final) possédant une unique responsabilité.
 */
final class RunDrawAction
{
    /**
     * Injection de dépendance via la promotion de propriété (PHP 8+).
     * Le mot-clé "readonly" garantit que l'instance du Resolver reste immuable.
     */
    public function __construct(
        private readonly DrawStrategyResolver $resolver
    ) {}

    /**
     * Point d'entrée de l'action.
     * Construit l'entité du Domaine (garantissant ses invariants), résout la stratégie,
     * puis délègue l'exécution du tirage à l'entité elle-même (Double Dispatch).
     */
    public function execute(DrawData $data): DrawResult
    {
        $strategy = $this->resolver->resolve($data->type);

        // La construction de Draw applique l'invariant "min. 2 participants"
        // avant même de tenter le tirage (Always-Valid Entity).
        $draw = new Draw($data->participantsCollection());

        return $draw->execute($strategy);
    }
}
