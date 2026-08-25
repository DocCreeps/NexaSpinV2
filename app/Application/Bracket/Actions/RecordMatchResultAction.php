<?php

namespace App\Application\Bracket\Actions;

use App\Domain\Bracket\Entities\Bracket;
use App\Domain\Bracket\ValueObjects\Participant;

/**
 * Action (Use Case) orchestrant l'enregistrement du résultat d'un match.
 * Rejoue l'historique des résultats déjà saisis pour reconstruire un Bracket
 * cohérent, puis applique le nouveau résultat (qui déclenche la propagation
 * automatique du vainqueur vers le tour suivant, gérée par l'entité Bracket).
 */
final class RecordMatchResultAction
{
    public function __construct(
        private readonly RebuildBracketAction $rebuildBracketAction,
    ) {}

    /**
     * @param array<int, string> $participantNames
     * @param array<int, array{round: int, position: int, winner: string}> $previousResults
     */
    public function execute(
        array $participantNames,
        array $previousResults,
        int $round,
        int $position,
        string $winnerName,
    ): Bracket {
        $bracket = $this->rebuildBracketAction->execute($participantNames, $previousResults);

        $bracket->recordResult($round, $position, new Participant($winnerName));

        return $bracket;
    }
}
