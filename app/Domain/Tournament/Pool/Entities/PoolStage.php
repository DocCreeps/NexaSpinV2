<?php

namespace App\Domain\Tournament\Pool\Entities;

use App\Domain\Tournament\Collections\Participants;
use App\Domain\Tournament\Pool\Exceptions\InvalidPoolStageException;
use App\Domain\Tournament\ValueObjects\Participant;

/**
 * Entité du Domaine (Aggregate Root) pour une phase de poules.
 *
 * Contrairement à un bracket (qui a besoin d'un nombre de participants en
 * puissance de 2 et comble les vides avec des byes), une phase de poules en
 * round-robin n'a JAMAIS besoin d'un compte "rond" : chaque poule joue tous
 * les matchs possibles entre ses membres, quelle que soit sa taille. La seule
 * décision à prendre est donc la répartition en poules aussi équilibrées que
 * possible (tailles ne différant jamais de plus de 1), ce qui garantit
 * mécaniquement zéro match vide, y compris quand le nombre de participants ne
 * tombe pas sur un multiple de la taille de poule visée.
 *
 * La taille des poules n'est jamais saisie par l'utilisateur : elle est
 * calculée automatiquement à partir du seul nombre de participants, en visant
 * des poules aussi proches que possible de IDEAL_POOL_SIZE (voir
 * resolvePoolCount()).
 */
final class PoolStage
{
    private const MIN_PARTICIPANTS = 4;

    private const MIN_POOL_SIZE = 3;

    private const MAX_POOL_SIZE = 6;

    /** Taille de poule "idéale" servant de référence pour l'algorithme automatique. */
    private const IDEAL_POOL_SIZE = 4;

    /** @var array<int, Pool> */
    private array $pools = [];

    public function __construct(Participants $participants)
    {
        $names = $participants->all();
        $count = count($names);

        $this->validate($count);

        $poolCount = $this->resolvePoolCount($count);
        $this->pools = $this->distribute($names, $poolCount);
    }

    private function validate(int $count): void
    {
        if ($count < self::MIN_PARTICIPANTS) {
            throw new InvalidPoolStageException(
                sprintf('A pool stage requires at least %d participants.', self::MIN_PARTICIPANTS)
            );
        }
    }

    /**
     * Détermine automatiquement le nombre de poules le plus adapté au nombre
     * de participants : parmi tous les découpages qui gardent chaque poule
     * dans [MIN_POOL_SIZE, MAX_POOL_SIZE], on retient celui dont la taille
     * moyenne de poule est la plus proche de IDEAL_POOL_SIZE (avec, en cas
     * d'égalité, une préférence pour la répartition la plus homogène — le
     * moins d'écart possible entre la plus petite et la plus grande poule).
     */
    private function resolvePoolCount(int $count): int
    {
        $bestPoolCount = 1;
        $bestScore = null;

        $maxPoolCount = intdiv($count, self::MIN_POOL_SIZE);

        for ($poolCount = 1; $poolCount <= $maxPoolCount; $poolCount++) {
            $base = intdiv($count, $poolCount);
            $remainder = $count % $poolCount;
            $minSize = $base;
            $maxSize = $base + ($remainder > 0 ? 1 : 0);

            if ($minSize < self::MIN_POOL_SIZE || $maxSize > self::MAX_POOL_SIZE) {
                continue;
            }

            $closeness = abs(($count / $poolCount) - self::IDEAL_POOL_SIZE);
            $evenness = $maxSize - $minSize;
            $score = ($closeness * 10) + $evenness;

            if ($bestScore === null || $score < $bestScore) {
                $bestScore = $score;
                $bestPoolCount = $poolCount;
            }
        }

        return $bestPoolCount;
    }

    /**
     * Répartit les participants en $poolCount poules dont les tailles ne
     * diffèrent jamais de plus d'une unité (certaines reçoivent un participant
     * de plus quand $count n'est pas un multiple exact de $poolCount).
     *
     * @param array<int, Participant> $names
     * @return array<int, Pool>
     */
    private function distribute(array $names, int $poolCount): array
    {
        $base = intdiv(count($names), $poolCount);
        $remainder = count($names) % $poolCount;

        $pools = [];
        $cursor = 0;

        for ($i = 0; $i < $poolCount; $i++) {
            $size = $base + ($i < $remainder ? 1 : 0);
            $members = array_slice($names, $cursor, $size);
            $cursor += $size;

            $pools[] = new Pool(self::poolLabel($i), $members);
        }

        return $pools;
    }

    private static function poolLabel(int $index): string
    {
        // A, B, ..., Z, puis AA, AB, ... si jamais plus de 26 poules.
        $label = '';
        $index++;

        while ($index > 0) {
            $index--;
            $label = chr(65 + ($index % 26)).$label;
            $index = intdiv($index, 26);
        }

        return "Poule {$label}";
    }

    /** @return array<int, Pool> */
    public function pools(): array
    {
        return $this->pools;
    }

    public function isComplete(): bool
    {
        foreach ($this->pools as $pool) {
            if (! $pool->isComplete()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Nombre total de matchs de la phase, tous égaux au nombre de paires
     * possibles dans chaque poule : aucun n'est un match vide.
     */
    public function totalMatches(): int
    {
        return array_sum(array_map(fn (Pool $p) => count($p->matches()), $this->pools));
    }

    public function poolByName(string $name): ?Pool
    {
        foreach ($this->pools as $pool) {
            if ($pool->name === $name) {
                return $pool;
            }
        }

        return null;
    }
}
