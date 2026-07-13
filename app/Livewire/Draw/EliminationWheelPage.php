<?php

namespace App\Livewire\Draw;

use Livewire\Attributes\Computed;
use Livewire\Component;

use App\Domain\Draw\Enums\DrawType;
use App\Domain\Draw\ValueObjects\Participant;

use App\Application\Draw\DTOs\DrawData;
use App\Application\Draw\Actions\RunDrawAction;
use App\Application\Draw\Support\WheelSegmentBuilder;

use App\Livewire\Draw\Concerns\ManagesParticipants;

class EliminationWheelPage extends Component
{
    use ManagesParticipants;

    private const MAX_LABELS_ON_WHEEL = 10;

    public ?string $error = null;
    public array $initialParticipants = [];
    public array $eliminated = [];
    public array $colors = [];
    public ?string $pendingElimination = null;
    public ?string $lastEliminated = null;
    public ?string $winner = null;
    public bool $processing = false;
    public int $wheelRotation = 0;
    public bool $autoMode = false;

    protected function participantsAreLocked(): bool
    {
        return $this->started();
    }

    /**
     * Déclenché automatiquement dès que l'interrupteur autoMode change côté client.
     */
    public function updatedAutoMode($value): void
    {
        if ($value && !$this->processing && !$this->winner) {
            $this->handleAction();
        }
    }

    /**
     * Point d'entrée unique du bouton principal.
     */
    public function handleAction(): void
    {
        if (!$this->started()) {
            $this->start();
            if ($this->error) {
                return;
            }
        }

        $this->eliminateNext();
    }

    /**
     * Initialisation et verrouillage du tirage.
     */
    public function start(): void
    {
        $this->error = null;

        if (count($this->participants) < 2) {
            $this->error = "Ajoutez au moins deux participants avant de lancer l'élimination.";
            return;
        }

        $this->initialParticipants = $this->participants;
        $this->colors = WheelSegmentBuilder::assignColors($this->participants);
        $this->eliminated = [];
        $this->winner = null;
        $this->pendingElimination = null;
        $this->lastEliminated = null;
        $this->processing = false;
        $this->wheelRotation = 0;

        unset($this->segments);

        $this->dispatch('start-ready');
    }

    /**
     * Sélectionne la cible et calcule son alignement géométrique parfait.
     */
    public function eliminateNext(): void
    {
        if ($this->processing || $this->winner || count($this->participants) <= 1) {
            return;
        }

        $this->processing = true;

        $draw = app(RunDrawAction::class)->execute(
            new DrawData(
                participants: collect($this->participants)->map(fn(string $name) => new Participant($name))->all(),
                type: DrawType::WHEEL
            )
        );

        $this->pendingElimination = $draw->name;

        $totalParticipants = count($this->participants);
        $targetIndex = array_search($this->pendingElimination, $this->participants);

        if ($targetIndex !== false && $totalParticipants > 0) {
            $arcDegres = 360 / $totalParticipants;

            // Calcule l'angle pour caler le centre du segment cible sous le pointeur du haut (à 0°/360°)
            $targetAngle = 360 - (($targetIndex * $arcDegres) + ($arcDegres / 2));
            $targetAngle = fmod($targetAngle, 360);
            if ($targetAngle < 0) {
                $targetAngle += 360;
            }

            // Ajoute minimum 5 tours complets (1800°) pour l'élan visuel
            $minRotation = $this->wheelRotation + 1800;
            $currentMod = fmod($minRotation, 360);

            if ($targetAngle >= $currentMod) {
                $nextRotation = $minRotation + ($targetAngle - $currentMod);
            } else {
                $nextRotation = $minRotation + (360 - $currentMod) + $targetAngle;
            }

            $deltaRotation = $nextRotation - $this->wheelRotation;
            $this->wheelRotation = (int)$nextRotation;

            $this->dispatch('wheel-spin', rotation: $deltaRotation);
        }
    }

    /**
     * Valide l'élimination dans les données après l'arrêt de la roue.
     */
    public function confirmElimination(): void
    {
        if (! $this->processing || ! $this->pendingElimination) {
            $this->processing = false;
            return;
        }

        $player = $this->pendingElimination;

        if (! in_array($player, $this->eliminated)) {
            $this->eliminated[] = $player;
            $this->lastEliminated = $player;
            $this->participants = array_values(array_diff($this->participants, [$player]));
        }

        $this->pendingElimination = null;

        if (count($this->participants) === 1) {
            $this->winner = $this->participants[0];
            $this->autoMode = false;
        }

        $this->processing = false;

        $this->dispatch('elimination-confirmed');
    }

    /**
     * Reset complet pour rejouer avec la même liste.
     */
    public function restart(): void
    {
        $this->participants = $this->initialParticipants;
        $this->initialParticipants = [];
        $this->eliminated = [];
        $this->colors = [];
        $this->pendingElimination = null;
        $this->lastEliminated = null;
        $this->winner = null;
        $this->processing = false;
        $this->error = null;
        $this->wheelRotation = 0;
        $this->autoMode = false;

        unset($this->segments);

        $this->dispatch('game-restarted');
    }

    public function started(): bool
    {
        return $this->colors !== [];
    }

    #[Computed]
    public function segments(): array
    {
        return WheelSegmentBuilder::build($this->participants, $this->colors);
    }

    public function showLabelsOnWheel(): bool
    {
        return count($this->participants) <= self::MAX_LABELS_ON_WHEEL;
    }

    public function render()
    {
        return view('livewire.draw.elimination-wheel-page')->layout('layouts.app');
    }
}
