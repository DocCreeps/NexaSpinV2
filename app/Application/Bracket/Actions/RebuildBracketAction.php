<?php

namespace App\Application\Bracket\Actions;

use App\Domain\Bracket\Collections\Participants;
use App\Domain\Bracket\Entities\Bracket;
use App\Domain\Bracket\ValueObjects\Participant;

final class RebuildBracketAction
{
    /**
     * @param array<int, string> $participants
     * @param array<int, array{round: int, position: int, winner: string}> $results
     */
    public function execute(array $participants, array $results): Bracket
    {
        $collection = new Participants(
            array_map(fn(string $name) => new Participant($name), $participants)
        );

        $bracket = new Bracket($collection);

        // Rejoue tous les résultats enregistrés
        foreach ($results as $result) {
            $bracket->recordResult(
                $result['round'],
                $result['position'],
                new Participant($result['winner'])
            );
        }

        return $bracket;
    }
}
