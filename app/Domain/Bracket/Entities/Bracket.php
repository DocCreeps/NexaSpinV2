<?php

namespace App\Domain\Bracket\Entities;

use App\Domain\Bracket\Collections\Participants;
use App\Domain\Bracket\Exceptions\InvalidBracketException;
use App\Domain\Bracket\ValueObjects\Participant;

/**
 * Entité du Domaine (Aggregate Root).
 * Construit l'arbre complet du tournoi à la création (padding à la puissance de
 * 2 supérieure, byes au round 1 uniquement) et propage chaque résultat saisi
 * manuellement vers le tour suivant.
 */
final class Bracket
{
    private const MIN_PARTICIPANTS = 4;

    /** @var array<int, array<int, BracketMatch>> Indexé par round (1-based) puis position (0-based). */
    private array $rounds = [];

    private readonly int $roundCount;

    public function __construct(Participants $participants)
    {
        $this->validate($participants);

        $names = $participants->all();
        $count = count($names);
        $size = self::nextPowerOfTwo($count);
        $this->roundCount = (int) log($size, 2);

        $totalRound1Slots = $size / 2; // Ex: 16 matchs au Round 1 pour un arbre de 32
        $byesCount = $size - $count;   // Ex: 32 - 20 = 12 BYEs
        $realMatchesCount = $count - $totalRound1Slots; // Ex: 20 - 16 = 4 VRAIS matchs (8 joueurs)

        $round1 = [];
        $playerIndex = 0;

        // On alterne les vrais matchs et les BYEs pour que la propagation vers le Round 2 soit parfaite
        for ($i = 0; $i < $totalRound1Slots; $i++) {
            if ($i < $realMatchesCount) {
                // Vrai match (2 joueurs s'affrontent)
                $round1[] = new BracketMatch(1, $i, $names[$playerIndex], $names[$playerIndex + 1]);
                $playerIndex += 2;
            } else {
                // Match BYE (1 joueur passe d'office, l'autre slot est null)
                $round1[] = new BracketMatch(1, $i, $names[$playerIndex] ?? null, null);
                $playerIndex++;
            }
        }

        $this->rounds[1] = $round1;

        // Construction des rounds suivants
        for ($round = 2; $round <= $this->roundCount; $round++) {
            $this->rounds[$round] = [];
            for ($i = 0; $i < $size / (2 ** $round); $i++) {
                $this->rounds[$round][] = new BracketMatch($round, $i);
            }
        }

        // Propage les BYEs vers le Round 2
        $this->propagateResolvedMatches();
    }

    /**
     * Règle métier (Invariant) : empêche la création d'un bracket avec trop peu
     * de participants (mode libre : pas de puissance de 2 imposée en entrée).
     */
    private function validate(Participants $participants): void
    {
        if ($participants->count() < self::MIN_PARTICIPANTS) {
            throw new InvalidBracketException(
                sprintf('A bracket requires at least %d participants.', self::MIN_PARTICIPANTS)
            );
        }
    }

    /**
     * Enregistre le résultat saisi pour un match donné et fait avancer le
     * gagnant vers le tour suivant.
     *
     * @throws InvalidBracketException Si le match (round, position) n'existe pas.
     */
    public function recordResult(int $round, int $position, Participant $winner): void
    {
        $match = $this->rounds[$round][$position] ?? null;

        if ($match === null) {
            throw new InvalidBracketException("Match introuvable (round {$round}, position {$position}).");
        }

        $match->recordResult($winner);
        $this->propagateResolvedMatches();
    }

    /**
     * Fait avancer chaque gagnant déjà connu vers le match du tour suivant
     * qui l'attend. Idempotent : peut être rappelée sans effet de bord.
     */
    private function propagateResolvedMatches(): void
    {
        for ($round = 1; $round < $this->roundCount; $round++) {
            foreach ($this->rounds[$round] as $match) {
                if (! $match->isResolved()) {
                    continue;
                }

                $nextMatch = $this->rounds[$round + 1][intdiv($match->position, 2)];
                $nextMatch->fillSlot($match->position % 2, $match->winner());
            }
        }
    }

    public function isComplete(): bool
    {
        return $this->rounds[$this->roundCount][0]->isResolved();
    }

    public function champion(): ?Participant
    {
        return $this->isComplete() ? $this->rounds[$this->roundCount][0]->winner() : null;
    }

    /**
     * @return array<int, array<int, BracketMatch>>
     */
    public function rounds(): array
    {
        return $this->rounds;
    }

    public function roundCount(): int
    {
        return $this->roundCount;
    }

    private static function nextPowerOfTwo(int $n): int
    {
        return 2 ** (int) ceil(log($n, 2));
    }
}
