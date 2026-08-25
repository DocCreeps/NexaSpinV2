<?php

namespace App\Application\Tournament\Pool\Actions;

use App\Domain\Tournament\Pool\Entities\PoolStage;
use App\Domain\Tournament\Pool\Exceptions\InvalidPoolMatchResultException;
use App\Domain\Tournament\ValueObjects\Participant;

final class RecordPoolMatchResultAction
{
    public function __construct(
        private readonly RebuildPoolStageAction $rebuildAction,
    ) {}

    /**
     * @param array<int, string> $participants
     * @param array<int, array{pool: string, matchIndex: int, winner: string}> $previousResults
     */
    public function execute(
        array $participants,
        array $previousResults,
        string $poolName,
        int $matchIndex,
        string $winnerName,
    ): PoolStage {
        $stage = $this->rebuildAction->execute($participants, $previousResults);

        $pool = $stage->poolByName($poolName);

        if ($pool === null) {
            throw new InvalidPoolMatchResultException("Poule introuvable : {$poolName}.");
        }

        $match = null;
        foreach ($pool->matches() as $candidate) {
            if ($candidate->index === $matchIndex) {
                $match = $candidate;
                break;
            }
        }

        if ($match === null) {
            throw new InvalidPoolMatchResultException("Match introuvable ({$poolName} #{$matchIndex}).");
        }

        $match->recordResult(new Participant($winnerName));

        return $stage;
    }
}
