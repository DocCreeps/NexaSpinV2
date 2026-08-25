<?php

namespace App\Application\Tournament\Pool\Actions;

use App\Domain\Tournament\Collections\Participants;
use App\Domain\Tournament\Pool\Entities\PoolStage;
use App\Domain\Tournament\ValueObjects\Participant;

final class RebuildPoolStageAction
{
    /**
     * @param array<int, string> $participants
     * @param array<int, array{pool: string, matchIndex: int, winner: string}> $results
     */
    public function execute(array $participants, array $results): PoolStage
    {
        $collection = new Participants(
            array_map(fn (string $name) => new Participant($name), $participants)
        );

        $stage = new PoolStage($collection);

        foreach ($results as $result) {
            $pool = $stage->poolByName($result['pool']);

            if ($pool === null) {
                continue;
            }

            foreach ($pool->matches() as $match) {
                if ($match->index === $result['matchIndex']) {
                    $match->recordResult(new Participant($result['winner']));
                    break;
                }
            }
        }

        return $stage;
    }
}
