<?php

namespace App\Application\Draw\Actions;

use App\Application\Draw\DTOs\DrawData;
use App\Application\Draw\Factories\DrawFactory;
use App\Domain\Draw\ValueObjects\Participant;

class RunDrawAction
{
    public function execute(DrawData $data): ?Participant
    {
        $strategy = DrawFactory::make($data->type);

        return $strategy->draw(
            $data->participants,
            $data->options,
        );
    }
}
