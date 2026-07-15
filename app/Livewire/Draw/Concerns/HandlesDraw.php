<?php

namespace App\Livewire\Draw\Concerns;

use App\Application\Draw\Actions\RunDrawAction;
use App\Application\Draw\DTOs\DrawData;
use App\Domain\Draw\Enums\DrawDisplay;
use App\Domain\Draw\Enums\DrawType;
use App\Domain\Draw\ValueObjects\DrawResult;
use RuntimeException;

/**
 * Trait Livewire centralisant l'exécution d'un tirage au sort.
 * Fait la passerelle entre l'état du composant (UI) et le cas d'utilisation (Application).
 */
trait HandlesDraw
{
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
        // Optionnel : on sécurise le fait que le composant doit posséder la propriété
        if (! property_exists($this, 'participants')) {
            throw new RuntimeException(
                sprintf('The component [%s] must define a $participants property to use HandlesDraw.', static::class)
            );
        }

        /** @var array<int, string> */
        return $this->participants;
    }
}
