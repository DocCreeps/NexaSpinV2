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
 * Page de tirage classique sous forme de Roue de la Fortune.
 */
class WheelPage extends Component
{
    use HandlesDraw;
    use ManagesParticipants;

    private const MAX_LABELS_ON_WHEEL = 10;
    private const MIN_PARTICIPANTS = 2;

    public ?string $result = null;

    /** Rotation cumulée appliquée à la roue (degrés) */
    public int $wheelRotation = 0;

    protected function afterParticipantsChanged(): void
    {
        $this->result = null;
    }

    public function draw(RunDrawAction $action): void
    {
        $this->error = null;

        if (count($this->participants) < self::MIN_PARTICIPANTS) {
            $this->error = 'Ajoutez au moins deux participants.';

            return;
        }

        $result = $this->executeDraw($action);
        $winner = $result->winner;

        $index = array_search($winner->name, $this->participants, true);

        if ($index === false) {
            $this->error = 'Gagnant introuvable.';

            return;
        }

        $this->result = $winner->name;

        // On calcule un delta pour Alpine (x-draw.wheel) qui additionne la rotation au lieu d'un angle absolu
        $rotation = WheelSegmentBuilder::cumulativeRotationFor(
            targetIndex: $index,
            total: count($this->participants),
            currentRotation: $this->wheelRotation,
        );

        $this->wheelRotation = $rotation['newRotation'];

        $this->dispatch('wheel-spin', rotation: $rotation['delta']);
    }

    public function canDraw(): bool
    {
        return count($this->participants) >= self::MIN_PARTICIPANTS;
    }

    /**
     * @return array<int, array{name: string, color: string, path: ?string, fullCircle: bool, labelTransform: string}>
     */
    #[Computed]
    public function segments(): array
    {
        return WheelSegmentBuilder::build($this->participants);
    }

    public function showLabelsOnWheel(): bool
    {
        return count($this->participants) <= self::MAX_LABELS_ON_WHEEL;
    }

    public function render()
    {
        $mode = DrawModeType::CLASSIC->toDto();

        return view('livewire.draw.wheel-page')
            ->layout('layouts.app', [
                'title' => $mode->metaTitle,
                'metaDescription' => $mode->metaDescription,
            ]);
    }
}
