<?php

namespace App\Application\Draw\Actions;


use App\Application\Draw\DTOs\DrawData;
use App\Application\Draw\Factories\DrawFactory;

use App\Domain\Draw\ValueObjects\DrawResult;



final class RunDrawAction
{

    public function execute(
        DrawData $data
    ): DrawResult {


        $strategy =
            DrawFactory::make(
                $data->type
            );


        return $strategy->draw(
            $data->participantsCollection()
        );
    }
}
