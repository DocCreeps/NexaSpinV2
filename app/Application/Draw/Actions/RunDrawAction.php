<?php

namespace App\Application\Draw\Actions;

use App\Application\Draw\DTOs\DrawData;
use App\Application\Draw\Factories\DrawFactory;

class RunDrawAction
{
    public function execute(DrawData $data): mixed
    {
        $strategy = DrawFactory::make($data->type);

        return $strategy->draw(
            $data->participants,
            $data->options,
        );
    }
}
