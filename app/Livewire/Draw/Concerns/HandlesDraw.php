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
                type: $this->drawType(),
                display: DrawDisplay::WHEEL,
                weights: $this->participantWeights,
            )
        );
    }

    /**
     * Type de tirage à utiliser par le composant hôte.
     * Par défaut : tirage aléatoire uniforme. Les composants pondérés
     * (ex: WeightedWheelPage) doivent surcharger cette méthode pour
     * activer réellement la prise en compte des poids.
     */
    protected function drawType(): DrawType
    {
        return DrawType::RANDOM;
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
