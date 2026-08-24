<?php

namespace App\Application\Teams;

/**
 * Répartit une liste de participants en un nombre donné d'équipes de taille égale.
 *
 * Si le nombre de participants n'est pas un multiple exact du nombre d'équipes,
 * les participants excédentaires ne sont assignés à aucune équipe : ils deviennent
 * des "remplaçants", à répartir manuellement si besoin.
 */
final class TeamsGenerator
{
    /**
     * @param  array<int, string>  $participants
     * @return array{teams: array<int, array<int, string>>, substitutes: array<int, string>}
     */
    public function generate(array $participants, int $teamsCount): array
    {
        $shuffled = array_values($participants);
        shuffle($shuffled);

        $perTeam = intdiv(count($shuffled), $teamsCount);
        $substitutesCount = count($shuffled) % $teamsCount;

        $substitutes = $substitutesCount > 0
            ? array_splice($shuffled, count($shuffled) - $substitutesCount)
            : [];

        $teams = array_fill(0, $teamsCount, []);

        foreach ($shuffled as $index => $name) {
            $teams[intdiv($index, $perTeam)][] = $name;
        }

        return [
            'teams' => array_values($teams),
            'substitutes' => $substitutes,
        ];
    }
}
