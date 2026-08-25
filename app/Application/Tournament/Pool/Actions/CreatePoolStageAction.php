<?php

namespace App\Application\Tournament\Pool\Actions;

use App\Application\Tournament\DTOs\ParticipantListData;
use App\Domain\Tournament\Pool\Entities\PoolStage;

/**
 * Action (Use Case) orchestrant la création initiale d'une phase de poules.
 */
final class CreatePoolStageAction
{
    public function execute(ParticipantListData $data): PoolStage
    {
        return new PoolStage($data->participantsCollection());
    }
}
