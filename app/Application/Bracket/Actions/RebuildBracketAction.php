<?php

namespace App\Application\Bracket\Actions;

use App\Application\Bracket\DTOs\BracketData;
use App\Domain\Bracket\Entities\Bracket;
use App\Domain\Bracket\ValueObjects\Participant;

/**
 * Action (Use Case) reconstruisant un Bracket cohérent à partir d'un état plat
 * (participants + résultats déjà saisis). Un composant Livewire ne stocke jamais
 * l'entité Bracket directement (non sérialisable proprement) : il ne conserve
 * que ce tableau plat, rejoué ici à chaque action serveur — même principe que
 * `HandlesDraw` qui reconstruit un `Draw` à chaque tirage.
 */
final class RebuildBracketAction
{
    public function __construct(
        private readonly CreateBracketAction $createBracketAction,
    ) {}

    /**
     * @param array<int, string> $participantNames
     * @param array<int, array{round: int, position: int, winner: string}> $results Dans l'ordre de saisie.
     */
    public function execute(array $participantNames, array $results): Bracket
    {
        $bracket = $this->createBracketAction->execute(new BracketData($participantNames));

        foreach ($results as $result) {
            $bracket->recordResult(
                $result['round'],
                $result['position'],
                new Participant($result['winner']),
            );
        }

        return $bracket;
    }
}
