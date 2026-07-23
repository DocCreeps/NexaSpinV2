<?php

namespace App\Livewire\Draw;

use App\Application\Draw\Actions\RunDrawAction;
use App\Application\Draw\Enums\DrawModeType;
use App\Application\Draw\Support\WheelSegmentBuilder;
use App\Livewire\Draw\Concerns\HandlesDraw;
use App\Livewire\Draw\Concerns\ManagesParticipants;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * Page de tirage de la Roue d'Élimination (Battle Royale).
 */
class EliminationWheelPage extends Component
{
    use HandlesDraw;
    use ManagesParticipants;

    private const MAX_LABELS_ON_WHEEL = 10;
    private const MIN_PARTICIPANTS = 5;

    /** Seuil d'abandon en secondes (animation frontend = 4.5s). */
    private const STUCK_THRESHOLD_SECONDS = 8.0;

    public ?string $error = null;

    /** @var array<int, string> */
    public array $initialParticipants = [];

    /** @var array<int, string> */
    public array $eliminated = [];

    /** @var array<string, string> */
    public array $colors = [];

    public ?string $pendingElimination = null;
    public ?string $lastEliminated = null;
    public ?string $winner = null;

    public bool $processing = false;
    public ?float $processingStartedAt = null;
    public int $wheelRotation = 0;
    public bool $autoMode = false;

    protected function participantsAreLocked(): bool
    {
        return $this->started();
    }

    public function updatedAutoMode(bool $value): void
    {
        if ($value && ! $this->processing && ! $this->winner) {
            $this->handleAction();
        }
    }

    public function handleAction(?RunDrawAction $action = null): void
    {
        $action = $action ?? app(RunDrawAction::class);

        if (! $this->started()) {
            $this->start();

            if ($this->error) {
                return;
            }
        }

        $this->eliminateNext($action);
    }

    public function start(): void
    {
        $this->error = null;

        if (count($this->participants) < self::MIN_PARTICIPANTS) {
            $this->error = sprintf(
                "Ajoutez au moins %d participants avant de lancer l'élimination.",
                self::MIN_PARTICIPANTS
            );

            return;
        }

        $this->initialParticipants = $this->participants;
        $this->colors = WheelSegmentBuilder::assignColors($this->participants);

        $this->eliminated = [];
        $this->winner = null;
        $this->pendingElimination = null;
        $this->lastEliminated = null;
        $this->processing = false;
        $this->processingStartedAt = null;
        $this->wheelRotation = 0;

        unset($this->segments);

        $this->dispatch('start-ready');
    }

    public function eliminateNext(?RunDrawAction $action = null): void
    {
        $action = $action ?? app(RunDrawAction::class);

        if ($this->processing || $this->winner || count($this->participants) <= 1) {
            return;
        }

        $this->processing = true;

        $result = $this->executeDraw($action);
        $this->pendingElimination = $result->winner->name;

        $targetIndex = array_search($this->pendingElimination, $this->participants, true);

        if ($targetIndex === false) {
            $this->processing = false;

            return;
        }

        $rotation = WheelSegmentBuilder::cumulativeRotationFor(
            targetIndex: $targetIndex,
            total: count($this->participants),
            currentRotation: $this->wheelRotation,
        );

        $this->wheelRotation = $rotation['newRotation'];
        $this->processingStartedAt = microtime(true);

        $this->dispatch('wheel-spin', rotation: $rotation['delta']);
    }

    /**
     * Confirme l'élimination du participant (appelé par Alpine.js fin d'animation).
     */
    public function confirmElimination(): void
    {
        if (! $this->processing || ! $this->pendingElimination) {
            $this->processing = false;

            return;
        }

        $player = $this->pendingElimination;

        if (! in_array($player, $this->eliminated, true)) {
            $this->eliminated[] = $player;
            $this->lastEliminated = $player;

            $this->participants = array_values(
                array_diff($this->participants, [$player])
            );

            $this->afterParticipantsChanged();
        }

        $this->pendingElimination = null;

        if (count($this->participants) === 1) {
            $this->winner = $this->participants[0];
            $this->autoMode = false;
        }

        $this->processing = false;
        $this->processingStartedAt = null;

        $this->dispatch('elimination-confirmed');
    }

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
        $this->processingStartedAt = null;
        $this->error = null;
        $this->wheelRotation = 0;
        $this->autoMode = false;

        unset($this->segments);

        $this->dispatch('game-restarted');
    }

    /**
     * Détecte un état "processing" bloqué (ex: déconnexion réseau durant le spin).
     */
    public function isStuck(): bool
    {
        return $this->processing
            && $this->processingStartedAt !== null
            && (microtime(true) - $this->processingStartedAt) > self::STUCK_THRESHOLD_SECONDS;
    }

    public function unstick(): void
    {
        if (! $this->isStuck()) {
            return;
        }

        $this->processing = false;
        $this->processingStartedAt = null;
        $this->pendingElimination = null;
        $this->error = "La roue semble bloquée : nouvelle tentative possible.";
    }

    public function started(): bool
    {
        return $this->colors !== [];
    }

    public function canStart(): bool
    {
        return count($this->participants) >= self::MIN_PARTICIPANTS;
    }

    /**
     * @return array<int, array{name: string, color: string, path: ?string, fullCircle: bool, labelTransform: string}>
     */
    #[Computed]
    public function segments(): array
    {
        return WheelSegmentBuilder::build(
            $this->participants,
            $this->colors
        );
    }

    public function showLabelsOnWheel(): bool
    {
        return count($this->participants) <= self::MAX_LABELS_ON_WHEEL;
    }

    public function render()
    {
        $mode = DrawModeType::ELIMINATION->toDto();

        return view('livewire.draw.elimination-wheel-page')
            ->layout('layouts.app', [
                'title' => $mode->metaTitle,
                'metaDescription' => $mode->metaDescription,
            ]);
    }
}
