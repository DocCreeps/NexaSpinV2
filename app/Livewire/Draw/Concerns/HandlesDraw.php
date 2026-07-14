<?php

namespace App\Livewire\Draw\Concerns;

use App\Application\Draw\Actions\RunDrawAction;
use App\Application\Draw\DTOs\DrawData;
use App\Domain\Draw\Enums\DrawDisplay;
use App\Domain\Draw\Enums\DrawType;

/**
 * Centralise l'exécution d'un tirage
 * depuis les composants Livewire.
 */
trait HandlesDraw
{
    protected function executeDraw(
        RunDrawAction $action
    ) {
        return $action->execute(
            new DrawData(
                participants: $this->participants,
                type: DrawType::RANDOM,
                display: DrawDisplay::WHEEL,
            )
        );
    }
}
