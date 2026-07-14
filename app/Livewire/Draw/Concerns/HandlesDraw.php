<?php

namespace App\Livewire\Draw\Concerns;

use App\Application\Draw\Actions\RunDrawAction;
use App\Application\Draw\DTOs\DrawData;
use App\Domain\Draw\Enums\DrawDisplay;
use App\Domain\Draw\Enums\DrawType;
use App\Domain\Draw\ValueObjects\DrawResult;

/**
 * Trait Livewire centralisant l'exécution d'un tirage au sort.
 * Fait la passerelle entre l'état du composant (UI) et le cas d'utilisation (Application).
 *
 * @property array<int, string> $participants Liste brute attendue sur le composant hôte.
 */
trait HandlesDraw
{
    /**
     * Instancie le DTO de transition et exécute l'action de tirage.
     *
     * Note technique : Ce helper encapsule la création du DTO "DrawData". Il s'attend à ce que
     * le composant Livewire d'accueil expose une propriété ou un getter `$this->participants`.
     */
    protected function executeDraw(
        RunDrawAction $action
    ): DrawResult {
        return $action->execute(
            new DrawData(
                participants: $this->participants,
                type: DrawType::RANDOM,
                display: DrawDisplay::WHEEL,
            )
        );
    }
}
