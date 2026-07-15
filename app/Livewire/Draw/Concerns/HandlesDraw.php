<?php

namespace App\Livewire\Draw\Concerns;

use App\Application\Draw\Actions\RunDrawAction;
use App\Application\Draw\DTOs\DrawData;
use App\Domain\Draw\Enums\DrawDisplay;
use App\Domain\Draw\Enums\DrawType;
use App\Domain\Draw\ValueObjects\DrawResult;

/**
 * Trait Livewire centralisant l'exécution d'un tirage au sort.
 */
trait HandlesDraw
{
    /**
     * @var array<int, string>
     */
    public array $participants = [];

    /**
     * Instancie le DTO de transition et exécute l'action de tirage.
     */
    protected function executeDraw(
        RunDrawAction $action
    ): DrawResult {
        return $action->execute(
            new DrawData(
                participants: $this->getParticipantsForDraw(),
                type: DrawType::RANDOM,
                display: DrawDisplay::WHEEL,
            )
        );
    }

    /**
     * Récupère et valide la liste brute des participants depuis le composant hôte.
     *
     * @return array<int, string>
     */
    private function getParticipantsForDraw(): array
    {
        // Plus besoin de property_exists() ! PHP assure lui-même que $participants est là.
        return $this->participants;
    }
}
