<?php

namespace App\Application\Draw\Actions;

use App\Application\Draw\DTOs\DrawData;

class EncodeDrawToUrlAction
{
    public function execute(DrawData $data): string
    {
        return base64_encode(
            json_encode([
                'participants' => $data->participants,
                'type' => $data->type->value,
                'options' => $data->options,
            ])
        );
    }
}
