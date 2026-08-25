<?php

namespace App\Application\Tournament\Bracket\Actions;

use App\Application\Tournament\DTOs\ParticipantListData;
use App\Domain\Tournament\Bracket\Entities\DoubleEliminationBracket;

/**
 * Action (Use Case) orchestrant la création initiale d'un bracket à double élimination.
 */
final class CreateDoubleEliminationBracketAction
{
    public function execute(ParticipantListData $data): DoubleEliminationBracket
    {
        return new DoubleEliminationBracket($data->participantsCollection());
    }
}
