<?php

namespace App\Domain\Tournament\Bracket\Entities;

use App\Domain\Tournament\Collections\Participants;
use App\Domain\Tournament\Bracket\Exceptions\InvalidBracketException;
use App\Domain\Tournament\ValueObjects\Participant;

/**
 * Entité du Domaine (Aggregate Root) pour un tournoi à double élimination.
 *
 * Construit trois structures :
 *  - Upper Bracket (UB) : identique au bracket simple élimination (byes au round 1).
 *  - Lower Bracket (LB) : repêchage des perdants de l'UB. 2*(R-1) rounds pour un UB
 *    de R rounds. Les rounds impairs ("mineurs") opposent des perdants du LB entre eux,
 *    les rounds pairs ("majeurs") opposent les rescapés du LB aux perdants fraîchement
 *    tombés de l'UB. Un bye côté UB round 1 ne produit aucun perdant : il "cascade"
 *    naturellement en bye côté LB round 1 pour ne pas générer de match vide.
 *  - Grande Finale : vainqueur UB contre vainqueur LB. Si le vainqueur LB l'emporte
 *    (il inflige sa première défaite au vainqueur UB), un match de "reset" décide
 *    du champion final.
 */
final class DoubleEliminationBracket
{
    private const MIN_PARTICIPANTS = 4;

    /** @var array<int, array<int, BracketMatch>> Upper bracket, indexé par round (1-based) puis position. */
    private array $upperRounds = [];

    /** @var array<int, array<int, BracketMatch>> Lower bracket, indexé par round (1-based) puis position. */
    private array $lowerRounds = [];

    /**
     * Pour chaque match du lower bracket, indique si son slot A / slot B recevra un
     * jour un participant réel. Calculé une fois à la construction à partir du seul
     * motif de byes de l'UB round 1 (aucune dépendance aux résultats). Sert à
     * distinguer trois cas : match normal (2 sources), "bye différé" (1 source,
     * résolu automatiquement dès que son unique source arrive), et match fantôme
     * (0 source, ignoré — ne recevra jamais aucun participant).
     *
     * @var array<int, array<int, array{a: bool, b: bool}>>
     */
    private array $lowerHasSource = [];

    private ?BracketMatch $grandFinal = null;

    private ?BracketMatch $grandFinalReset = null;

    private readonly int $upperRoundCount;

    /** Nombre de rounds du lower bracket : 2*(upperRoundCount - 1), 0 si upperRoundCount = 1. */
    private readonly int $lowerRoundCount;

    private readonly int $size;

    public function __construct(Participants $participants)
    {
        $this->validate($participants);

        $names = $participants->all();
        $count = count($names);
        $this->size = self::nextPowerOfTwo($count);
        $this->upperRoundCount = (int) log($this->size, 2);
        $this->lowerRoundCount = max(0, 2 * ($this->upperRoundCount - 1));

        $this->buildUpperBracket($names, $count);
        $this->buildLowerBracketShell();
        $this->computeLowerBracketSources();
        $this->applyPotentialByes();
        $this->buildGrandFinalShell();

        $this->propagate();
    }

    private function validate(Participants $participants): void
    {
        if ($participants->count() < self::MIN_PARTICIPANTS) {
            throw new InvalidBracketException(
                sprintf('A double elimination bracket requires at least %d participants.', self::MIN_PARTICIPANTS)
            );
        }
    }

    /**
     * @param array<int, Participant> $names
     */
    private function buildUpperBracket(array $names, int $count): void
    {
        $totalRound1Slots = $this->size / 2;
        $byesCount = $this->size - $count;
        $realMatchesCount = $count - $totalRound1Slots;

        $round1 = [];
        $playerIndex = 0;

        for ($i = 0; $i < $totalRound1Slots; $i++) {
            if ($i < $realMatchesCount) {
                $round1[] = new BracketMatch(1, $i, $names[$playerIndex], $names[$playerIndex + 1]);
                $playerIndex += 2;
            } else {
                $round1[] = new BracketMatch(1, $i, $names[$playerIndex] ?? null, null);
                $playerIndex++;
            }
        }

        $this->upperRounds[1] = $round1;

        for ($round = 2; $round <= $this->upperRoundCount; $round++) {
            $this->upperRounds[$round] = [];
            for ($i = 0; $i < $this->size / (2 ** $round); $i++) {
                $this->upperRounds[$round][] = new BracketMatch($round, $i);
            }
        }
    }

    /**
     * Crée les matchs "coquilles" du lower bracket (sans participants). Ils seront
     * remplis par la propagation, exactement comme le round 2+ de l'upper bracket.
     */
    private function buildLowerBracketShell(): void
    {
        for ($round = 1; $round <= $this->lowerRoundCount; $round++) {
            $i = intdiv($round + 1, 2); // paire (round, round+1 impair/pair) partagent le même nombre de matchs
            $matches = (int) ($this->size / (2 ** ($i + 1)));
            $matches = max($matches, 1);

            $this->lowerRounds[$round] = [];
            for ($i = 0; $i < $matches; $i++) {
                $this->lowerRounds[$round][] = new BracketMatch($round, $i);
            }
        }
    }

    /**
     * Calcule, pour chaque match du LB, si chacun de ses deux slots recevra un jour
     * un participant réel. Seule la "chaîne des rounds mineurs" (impairs) peut être
     * en manque de source, puisqu'elle remonte in fine aux byes de l'UB round 1 ; le
     * slot B des rounds majeurs vient toujours d'un round UB >= 2, qui ne connaît
     * jamais de bye dans ce domaine.
     */
    private function computeLowerBracketSources(): void
    {
        $hasWinner = []; // [round][position] => bool

        for ($round = 1; $round <= $this->lowerRoundCount; $round++) {
            $this->lowerHasSource[$round] = [];

            foreach ($this->lowerRounds[$round] as $match) {
                $position = $match->position;

                if ($round === 1) {
                    $a = ! $this->upperRounds[1][2 * $position]->isBye();
                    $b = ! $this->upperRounds[1][2 * $position + 1]->isBye();
                } elseif ($round % 2 === 0) {
                    // Round majeur : slot A <- round mineur précédent (même position), slot B <- UB (toujours réel).
                    $a = $hasWinner[$round - 1][$position] ?? false;
                    $b = true;
                } else {
                    // Round mineur (>1) : slot A/B <- les deux matchs du round majeur précédent, réduction 2 vers 1.
                    $a = $hasWinner[$round - 1][2 * $position] ?? false;
                    $b = $hasWinner[$round - 1][2 * $position + 1] ?? false;
                }

                $this->lowerHasSource[$round][$position] = ['a' => $a, 'b' => $b];
                $hasWinner[$round][$position] = $a || $b;
            }
        }
    }

    /**
     * Marque comme "bye différé" tout match du LB qui n'a structurellement qu'une
     * seule source réelle : dès qu'elle arrivera, le participant avancera seul,
     * sans attendre indéfiniment un adversaire qui ne viendra jamais.
     */
    private function applyPotentialByes(): void
    {
        foreach ($this->lowerHasSource as $round => $positions) {
            foreach ($positions as $position => $sources) {
                if ($sources['a'] xor $sources['b']) {
                    $this->lowerRounds[$round][$position]->markAsPotentialBye();
                }
            }
        }
    }

    private function buildGrandFinalShell(): void
    {
        if ($this->upperRoundCount >= 1) {
            $this->grandFinal = new BracketMatch(0, 0);
            $this->grandFinalReset = new BracketMatch(0, 1);
        }
    }

    public function recordUpperResult(int $round, int $position, Participant $winner): void
    {
        $this->recordResultOn($this->upperRounds, $round, $position, $winner);
        $this->propagate();
    }

    public function recordLowerResult(int $round, int $position, Participant $winner): void
    {
        $this->recordResultOn($this->lowerRounds, $round, $position, $winner);
        $this->propagate();
    }

    public function recordGrandFinalResult(Participant $winner): void
    {
        if ($this->grandFinal === null || ! $this->grandFinal->isPlayable()) {
            throw new InvalidBracketException('La grande finale n’est pas encore jouable.');
        }

        $this->grandFinal->recordResult($winner);
        $this->propagate();
    }

    public function recordGrandFinalResetResult(Participant $winner): void
    {
        if ($this->grandFinalReset === null || ! $this->grandFinalReset->isPlayable()) {
            throw new InvalidBracketException('Le match de reset n’est pas jouable.');
        }

        $this->grandFinalReset->recordResult($winner);
    }

    /**
     * @param array<int, array<int, BracketMatch>> $rounds
     */
    private function recordResultOn(array $rounds, int $round, int $position, Participant $winner): void
    {
        $match = $rounds[$round][$position] ?? null;

        if ($match === null) {
            throw new InvalidBracketException("Match introuvable (round {$round}, position {$position}).");
        }

        $match->recordResult($winner);
    }

    /**
     * Fait avancer les gagnants de l'UB (vers l'UB suivant + le LB) et du LB
     * (vers le LB suivant), puis alimente la grande finale. Idempotent.
     */
    private function propagate(): void
    {
        $this->propagateUpperBracket();
        $this->propagateLowerBracket();
        $this->propagateGrandFinal();
    }

    private function propagateUpperBracket(): void
    {
        // On inclut le round final : il ne fait pas avancer de vainqueur vers un
        // "round + 1" côté UB (c'est propagateGrandFinal qui s'en charge), mais son
        // perdant doit tout de même être repêché en LB final.
        for ($round = 1; $round <= $this->upperRoundCount; $round++) {
            foreach ($this->upperRounds[$round] as $match) {
                if (! $match->isResolved()) {
                    continue;
                }

                if ($round < $this->upperRoundCount) {
                    $nextMatch = $this->upperRounds[$round + 1][intdiv($match->position, 2)];
                    $nextMatch->fillSlot($match->position % 2, $match->winner());
                }

                $this->dropLoserToLowerBracket($match, $round);
            }
        }
    }

    /**
     * Envoie le perdant d'un match UB vers le lower bracket. Un bye UB round 1
     * n'a pas de perdant : le slot correspondant du LB round 1 reste donc "à moitié
     * vide" et deviendra lui-même un bye lors de la propagation du LB (aucun match
     * n'est jamais créé pour rien : c'est le même mécanisme que les byes de l'UB).
     */
    private function dropLoserToLowerBracket(BracketMatch $match, int $upperRound): void
    {
        $loser = $this->loserOf($match);

        if ($loser === null) {
            return; // Bye : personne à repêcher.
        }

        if ($upperRound === 1) {
            $lbMatch = $this->lowerRounds[1][intdiv($match->position, 2)];
            $lbMatch->fillSlot($match->position % 2, $loser);

            return;
        }

        // Perdant de l'UB round k (k>=2) : rejoint le LB round majeur 2*(k-1).
        $lbRound = 2 * ($upperRound - 1);
        $lbMatch = $this->lowerRounds[$lbRound][$match->position] ?? null;
        $lbMatch?->fillSlot(1, $loser);
    }

    private function propagateLowerBracket(): void
    {
        for ($round = 1; $round < $this->lowerRoundCount; $round++) {
            foreach ($this->lowerRounds[$round] as $match) {
                if (! $match->isResolved()) {
                    continue;
                }

                $nextRound = $round + 1;
                $nextPosition = $round % 2 === 1
                    ? $match->position // round mineur -> le vainqueur occupe le slot A du round majeur suivant
                    : intdiv($match->position, 2); // round majeur -> se réduit vers le prochain round mineur

                $nextMatch = $this->lowerRounds[$nextRound][$nextPosition] ?? null;
                $slot = $round % 2 === 1 ? 0 : ($match->position % 2);
                $nextMatch?->fillSlot($slot, $match->winner());
            }
        }
    }

    private function propagateGrandFinal(): void
    {
        if ($this->grandFinal === null) {
            return;
        }

        $upperChampion = $this->upperRounds[$this->upperRoundCount][0]->winner();

        $lowerChampion = $this->lowerRoundCount === 0
            ? null // UB à 1 seul round (4 joueurs, 1 round... en pratique upperRoundCount >= 2)
            : $this->lowerRounds[$this->lowerRoundCount][0]->winner();

        if ($upperChampion !== null) {
            $this->grandFinal->fillSlot(0, $upperChampion);
        }

        if ($lowerChampion !== null) {
            $this->grandFinal->fillSlot(1, $lowerChampion);
        }

        // Reset : uniquement nécessaire si le vainqueur LB remporte la grande finale.
        if ($this->grandFinal->isResolved() && $lowerChampion !== null
            && $this->grandFinal->winner()?->equals($lowerChampion)) {
            $this->grandFinalReset?->fillSlot(0, $upperChampion);
            $this->grandFinalReset?->fillSlot(1, $lowerChampion);
        }
    }

    private function loserOf(BracketMatch $match): ?Participant
    {
        $winner = $match->winner();

        if ($winner === null) {
            return null;
        }

        $a = $match->participantA();
        $b = $match->participantB();

        if ($a !== null && ! $a->equals($winner)) {
            return $a;
        }

        if ($b !== null && ! $b->equals($winner)) {
            return $b;
        }

        return null;
    }

    /**
     * Indique si le résultat d'un match a déjà eu un effet visible et résolu
     * sur un autre match (vainqueur propagé et/ou perdant repêché en lower
     * bracket dans un match lui-même déjà résolu). Sert à interdire la
     * modification d'un résultat dont la "propagation" a déjà été rejouée
     * plus loin dans l'arbre — la changer casserait la cohérence de tout ce
     * qui a été enregistré après, sans qu'on puisse le réparer autrement
     * qu'en effaçant ces résultats suivants.
     */
    public function hasDownstreamResult(string $section, ?int $round, ?int $position): bool
    {
        return match ($section) {
            'upper' => $round !== null && $position !== null && $this->upperHasDownstreamResult($round, $position),
            'lower' => $round !== null && $position !== null && $this->lowerHasDownstreamResult($round, $position),
            'grand_final' => $this->grandFinalReset?->isResolved() ?? false,
            'grand_final_reset' => false,
            default => false,
        };
    }

    private function upperHasDownstreamResult(int $round, int $position): bool
    {
        $match = $this->upperRounds[$round][$position] ?? null;

        if ($match === null || ! $match->isResolved()) {
            return false;
        }

        // Le vainqueur avance vers le round UB suivant (ou vers la grande finale
        // s'il s'agit du dernier round de l'UB).
        if ($round < $this->upperRoundCount) {
            $next = $this->upperRounds[$round + 1][intdiv($position, 2)] ?? null;
            if ($next?->isResolved()) {
                return true;
            }
        } elseif ($this->grandFinal?->isResolved()) {
            return true;
        }

        // Le perdant est repêché en lower bracket : si ce match-là est déjà résolu...
        if ($this->loserOf($match) === null) {
            return false; // bye : personne à repêcher, rien à casser en aval.
        }

        $lbMatch = $round === 1
            ? ($this->lowerRounds[1][intdiv($position, 2)] ?? null)
            : ($this->lowerRounds[2 * ($round - 1)][$position] ?? null);

        return $lbMatch?->isResolved() ?? false;
    }

    private function lowerHasDownstreamResult(int $round, int $position): bool
    {
        if ($round < $this->lowerRoundCount) {
            $nextRound = $round + 1;
            $nextPosition = $round % 2 === 1 ? $position : intdiv($position, 2);
            $next = $this->lowerRounds[$nextRound][$nextPosition] ?? null;

            return $next?->isResolved() ?? false;
        }

        // Dernier round du LB : le vainqueur avance vers la grande finale.
        return $this->grandFinal?->isResolved() ?? false;
    }

    public function isComplete(): bool
    {
        if ($this->grandFinal === null || ! $this->grandFinal->isResolved()) {
            return false;
        }

        $lowerWonFirstLeg = $this->grandFinal->winner()?->equals(
            $this->lowerRoundCount === 0 ? new Participant('__none__') : ($this->lowerRounds[$this->lowerRoundCount][0]->winner() ?? new Participant('__none__'))
        ) ?? false;

        if (! $lowerWonFirstLeg) {
            return true;
        }

        return $this->grandFinalReset?->isResolved() ?? false;
    }

    public function champion(): ?Participant
    {
        if (! $this->isComplete()) {
            return null;
        }

        return $this->grandFinalReset?->isResolved()
            ? $this->grandFinalReset->winner()
            : $this->grandFinal?->winner();
    }

    /** @return array<int, array<int, BracketMatch>> */
    public function upperRounds(): array
    {
        return $this->upperRounds;
    }

    /** @return array<int, array<int, BracketMatch>> */
    public function lowerRounds(): array
    {
        return $this->lowerRounds;
    }

    public function grandFinal(): ?BracketMatch
    {
        return $this->grandFinal;
    }

    public function grandFinalReset(): ?BracketMatch
    {
        return $this->grandFinalReset;
    }

    public function upperRoundCount(): int
    {
        return $this->upperRoundCount;
    }

    public function lowerRoundCount(): int
    {
        return $this->lowerRoundCount;
    }

    /**
     * Nombre de sources réelles attendues pour un match du lower bracket (0, 1 ou 2).
     * 0 = match fantôme, à ignorer totalement côté UI (ne recevra jamais personne).
     * 1 = bye différé : un seul participant arrivera, il avance automatiquement.
     * 2 = match normal.
     */
    public function lowerMatchSourceCount(int $round, int $position): int
    {
        $sources = $this->lowerHasSource[$round][$position] ?? null;

        if ($sources === null) {
            return 0;
        }

        return ($sources['a'] ? 1 : 0) + ($sources['b'] ? 1 : 0);
    }

    private static function nextPowerOfTwo(int $n): int
    {
        return 2 ** (int) ceil(log($n, 2));
    }
}
