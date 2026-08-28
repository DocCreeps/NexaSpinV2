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
        $shuffled = self::secureShuffle($participants);

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

    /**
     * Mélange Fisher-Yates via random_int() (CSPRNG), au lieu de shuffle()
     * (Mersenne Twister non cryptographique) — même exigence d'aléatoire que
     * le reste des tirages de l'application (voir Participants::random()).
     *
     * @param  array<int, string>  $participants
     * @return array<int, string>
     */
    private static function secureShuffle(array $participants): array
    {
        $items = array_values($participants);

        for ($i = count($items) - 1; $i > 0; $i--) {
            $j = random_int(0, $i);
            [$items[$i], $items[$j]] = [$items[$j], $items[$i]];
        }

        return $items;
    }
}
