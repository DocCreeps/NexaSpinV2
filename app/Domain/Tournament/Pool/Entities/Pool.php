<?php

namespace App\Domain\Tournament\Pool\Entities;

use App\Domain\Tournament\Pool\Exceptions\InvalidPoolStageException;
use App\Domain\Tournament\ValueObjects\Participant;

/**
 * Entité représentant une poule (groupe) : tous ses participants s'affrontent
 * une fois chacun (round-robin). Le calendrier est généré par la méthode du
 * cercle ("circle method"), qui répartit les matchs en journées où chaque
 * participant joue au plus une fois. Si la poule a un nombre impair de
 * participants, un participant est au repos à tour de rôle sur chaque
 * journée — ce n'est jamais un "match vide" puisqu'aucun PoolMatch n'est créé
 * pour un repos : seuls des matchs à deux participants réels existent.
 *
 * La liste à plat des matchs (matches()), utilisée pour l'affichage et la
 * saisie séquentielle des résultats, est en plus réordonnée pour qu'un même
 * participant n'enchaîne jamais deux matchs consécutifs dans cet ordre :
 * voir orderDaysToAvoidBackToBack().
 */
final class Pool
{
    private const MIN_SIZE = 2;

    /** @var array<int, Participant> */
    private readonly array $participants;

    /** @var array<int, PoolMatch> Tous les matchs de la poule, à plat. */
    private array $matches = [];

    /** @var array<int, array<int, PoolMatch>> Les mêmes matchs, groupés par journée. */
    private array $matchdays = [];

    public function __construct(
        public readonly string $name,
        array $participants,
    ) {
        if (count($participants) < self::MIN_SIZE) {
            throw new InvalidPoolStageException(
                sprintf('A pool requires at least %d participants.', self::MIN_SIZE)
            );
        }

        $this->participants = array_values($participants);
        $this->buildSchedule();
    }

    /**
     * Génère le calendrier round-robin via la méthode du cercle : un participant
     * fixe en position 0, les autres tournent d'un cran à chaque journée.
     * Les paires de chaque journée sont ensuite réordonnées (sans changer leur
     * répartition par journée) pour éviter qu'un participant n'enchaîne deux
     * matchs consécutifs dans la liste à plat des matchs.
     */
    private function buildSchedule(): void
    {
        $players = $this->participants;

        if (count($players) % 2 !== 0) {
            $players[] = null; // jeton "repos", jamais transformé en match
        }

        $n = count($players);
        $rounds = $n - 1;
        $half = intdiv($n, 2);

        /** @var array<int, array<int, array{0: Participant, 1: Participant}>> $rawDays */
        $rawDays = [];

        for ($round = 0; $round < $rounds; $round++) {
            $day = [];

            for ($i = 0; $i < $half; $i++) {
                $a = $players[$i];
                $b = $players[$n - 1 - $i];

                if ($a !== null && $b !== null) {
                    $day[] = [$a, $b];
                }
            }

            $rawDays[] = $day;

            // Rotation : tout le monde tourne d'un cran sauf le premier joueur (fixe).
            $last = $players[$n - 1];
            for ($i = $n - 1; $i > 1; $i--) {
                $players[$i] = $players[$i - 1];
            }
            $players[1] = $last;
        }

        $matchIndex = 0;

        foreach ($this->orderDaysToAvoidBackToBack($rawDays) as $day) {
            $dayMatches = [];

            foreach ($day as [$a, $b]) {
                $match = new PoolMatch($matchIndex++, $a, $b);
                $dayMatches[] = $match;
                $this->matches[] = $match;
            }

            $this->matchdays[] = $dayMatches;
        }
    }

    /**
     * Réordonne les paires de chaque journée (l'ensemble des journées et leur
     * contenu reste inchangé, seul l'ordre interne varie) pour qu'aucun
     * participant ne se retrouve dans le dernier match d'une journée puis le
     * premier match de la journée suivante — ce qui, dans la liste à plat des
     * matchs utilisée pour la saisie, lui ferait enchaîner deux matchs sans
     * aucun autre match entre les deux. Best-effort : dans une poule trop
     * petite (une seule paire par journée), ce n'est structurellement pas
     * toujours évitable.
     *
     * @param array<int, array<int, array{0: Participant, 1: Participant}>> $days
     * @return array<int, array<int, array{0: Participant, 1: Participant}>>
     */
    private function orderDaysToAvoidBackToBack(array $days): array
    {
        $ordered = [];
        $previousLastPair = null;

        foreach ($days as $day) {
            $day = $this->moveCleanPairFirst($day, $previousLastPair);
            $ordered[] = $day;
            $previousLastPair = $day === [] ? null : $day[array_key_last($day)];
        }

        return $ordered;
    }

    /**
     * Place en tête de $pairs la première paire ne partageant aucun
     * participant avec $bannedPair (le dernier match de la journée
     * précédente), pour casser l'enchaînement. Ne fait rien si $bannedPair
     * est null, si $pairs a moins de deux éléments, ou si aucune paire
     * "propre" n'existe (poule trop petite pour l'éviter).
     *
     * @param array<int, array{0: Participant, 1: Participant}> $pairs
     * @param array{0: Participant, 1: Participant}|null $bannedPair
     * @return array<int, array{0: Participant, 1: Participant}>
     */
    private function moveCleanPairFirst(array $pairs, ?array $bannedPair): array
    {
        if ($bannedPair === null || count($pairs) < 2) {
            return $pairs;
        }

        [$bannedA, $bannedB] = $bannedPair;

        $cleanIndex = null;

        foreach ($pairs as $i => [$a, $b]) {
            $touchesBanned = $a->equals($bannedA) || $a->equals($bannedB)
                || $b->equals($bannedA) || $b->equals($bannedB);

            if (! $touchesBanned) {
                $cleanIndex = $i;
                break;
            }
        }

        if ($cleanIndex === null || $cleanIndex === 0) {
            return $pairs;
        }

        $clean = $pairs[$cleanIndex];
        unset($pairs[$cleanIndex]);

        return array_merge([$clean], array_values($pairs));
    }

    public function size(): int
    {
        return count($this->participants);
    }

    /** @return array<int, Participant> */
    public function participants(): array
    {
        return $this->participants;
    }

    /** @return array<int, PoolMatch> */
    public function matches(): array
    {
        return $this->matches;
    }

    /** @return array<int, array<int, PoolMatch>> */
    public function matchdays(): array
    {
        return $this->matchdays;
    }

    public function isComplete(): bool
    {
        foreach ($this->matches as $match) {
            if (! $match->isResolved()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Classement basé sur les points (victoire = 3, nul = 1, défaite = 0),
     * ordre décroissant, départagé par le nombre de victoires. En cas
     * d'égalité totale, l'ordre d'origine des participants est conservé
     * (tri stable).
     *
     * @return array<int, array{participant: Participant, wins: int, draws: int, losses: int, played: int, points: int}>
     */
    public function standings(): array
    {
        $wins = [];
        $draws = [];
        $losses = [];
        $played = [];

        foreach ($this->participants as $participant) {
            $wins[$participant->name] = 0;
            $draws[$participant->name] = 0;
            $losses[$participant->name] = 0;
            $played[$participant->name] = 0;
        }

        foreach ($this->matches as $match) {
            if (! $match->isResolved()) {
                continue;
            }

            $nameA = $match->participantA()->name;
            $nameB = $match->participantB()->name;

            $played[$nameA]++;
            $played[$nameB]++;

            if ($match->isDraw()) {
                $draws[$nameA]++;
                $draws[$nameB]++;

                continue;
            }

            $winnerName = $match->winner()->name;
            $loserName = $winnerName === $nameA ? $nameB : $nameA;

            $wins[$winnerName]++;
            $losses[$loserName]++;
        }

        $standings = array_map(
            fn (Participant $p) => [
                'participant' => $p,
                'wins' => $wins[$p->name],
                'draws' => $draws[$p->name],
                'losses' => $losses[$p->name],
                'played' => $played[$p->name],
                'points' => ($wins[$p->name] * 2) + $draws[$p->name],
            ],
            $this->participants
        );

        usort(
            $standings,
            fn ($a, $b) => $b['points'] <=> $a['points'] ?: $b['wins'] <=> $a['wins']
        );

        return $standings;
    }
}
