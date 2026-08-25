<?php

namespace App\Application\Tournament\Bracket\Actions;

use App\Domain\Tournament\Collections\Participants;
use App\Domain\Tournament\Bracket\Entities\DoubleEliminationBracket;
use App\Domain\Tournament\ValueObjects\Participant;

/**
 * Rejoue l'historique des résultats déjà saisis pour reconstruire un
 * DoubleEliminationBracket cohérent. Chaque résultat précise sa "section"
 * (upper / lower / grand_final / grand_final_reset) puisque, contrairement au
 * bracket simple, il y a plusieurs arbres de matchs distincts à rejouer.
 */
final class RebuildDoubleEliminationBracketAction
{
    /**
     * @param array<int, string> $participants
     * @param array<int, array{section: string, round: int|null, position: int|null, winner: string}> $results
     */
    public function execute(array $participants, array $results): DoubleEliminationBracket
    {
        $collection = new Participants(
            array_map(fn (string $name) => new Participant($name), $participants)
        );

        $bracket = new DoubleEliminationBracket($collection);

        foreach ($results as $result) {
            $winner = new Participant($result['winner']);

            match ($result['section']) {
                'upper' => $bracket->recordUpperResult($result['round'], $result['position'], $winner),
                'lower' => $bracket->recordLowerResult($result['round'], $result['position'], $winner),
                'grand_final' => $bracket->recordGrandFinalResult($winner),
                'grand_final_reset' => $bracket->recordGrandFinalResetResult($winner),
                default => null,
            };
        }

        return $bracket;
    }
}
