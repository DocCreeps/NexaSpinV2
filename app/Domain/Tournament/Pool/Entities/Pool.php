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
        $matchIndex = 0;

        for ($round = 0; $round < $rounds; $round++) {
            $day = [];

            for ($i = 0; $i < $half; $i++) {
                $a = $players[$i];
                $b = $players[$n - 1 - $i];

                if ($a !== null && $b !== null) {
                    $match = new PoolMatch($matchIndex++, $a, $b);
                    $day[] = $match;
                    $this->matches[] = $match;
                }
            }

            $this->matchdays[] = $day;

            // Rotation : tout le monde tourne d'un cran sauf le premier joueur (fixe).
            $last = $players[$n - 1];
            for ($i = $n - 1; $i > 1; $i--) {
                $players[$i] = $players[$i - 1];
            }
            $players[1] = $last;
        }
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
     * Classement basé sur le nombre de victoires (ordre décroissant). En cas
     * d'égalité, l'ordre d'origine des participants est conservé (tri stable).
     *
     * @return array<int, array{participant: Participant, wins: int, played: int}>
     */
    public function standings(): array
    {
        $wins = [];
        $played = [];

        foreach ($this->participants as $participant) {
            $wins[$participant->name] = 0;
            $played[$participant->name] = 0;
        }

        foreach ($this->matches as $match) {
            if (! $match->isResolved()) {
                continue;
            }

            $played[$match->participantA()->name]++;
            $played[$match->participantB()->name]++;
            $wins[$match->winner()->name]++;
        }

        $standings = array_map(
            fn (Participant $p) => [
                'participant' => $p,
                'wins' => $wins[$p->name],
                'played' => $played[$p->name],
            ],
            $this->participants
        );

        usort($standings, fn ($a, $b) => $b['wins'] <=> $a['wins']);

        return $standings;
    }
}
