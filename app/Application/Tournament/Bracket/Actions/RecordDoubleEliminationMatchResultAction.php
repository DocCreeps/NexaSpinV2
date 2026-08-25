<?php

namespace App\Application\Tournament\Bracket\Actions;

use App\Domain\Tournament\Bracket\Entities\DoubleEliminationBracket;
use App\Domain\Tournament\ValueObjects\Participant;

final class RecordDoubleEliminationMatchResultAction
{
    public function __construct(
        private readonly RebuildDoubleEliminationBracketAction $rebuildAction,
    ) {}

    /**
     * @param array<int, string> $participantNames
     * @param array<int, array{section: string, round: int|null, position: int|null, winner: string}> $previousResults
     */
    public function execute(
        array $participantNames,
        array $previousResults,
        string $section,
        ?int $round,
        ?int $position,
        string $winnerName,
    ): DoubleEliminationBracket {
        $bracket = $this->rebuildAction->execute($participantNames, $previousResults);
        $winner = new Participant($winnerName);

        match ($section) {
            'upper' => $bracket->recordUpperResult($round, $position, $winner),
            'lower' => $bracket->recordLowerResult($round, $position, $winner),
            'grand_final' => $bracket->recordGrandFinalResult($winner),
            'grand_final_reset' => $bracket->recordGrandFinalResetResult($winner),
            default => throw new \InvalidArgumentException("Section de bracket inconnue : {$section}"),
        };

        return $bracket;
    }
}
