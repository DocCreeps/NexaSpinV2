<?php

namespace App\Livewire\Draw;

use App\Application\Draw\Actions\RunDrawAction;
use App\Application\Draw\Enums\DrawModeType;
use App\Application\Draw\Support\WheelSegmentBuilder;
use App\Domain\Draw\Enums\DrawType;
use App\Livewire\Draw\Concerns\HandlesDraw;
use App\Livewire\Draw\Concerns\ManagesParticipants;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * Composant de tirage au sort pondéré (roue de la fortune avec probabilités).
 */
class WeightedWheelPage extends Component
{
    use HandlesDraw;
    use ManagesParticipants;

    private const MAX_LABELS_ON_WHEEL = 10;
    private const MIN_PARTICIPANTS = 3;

    public ?string $result = null;

    /** Rotation cumulée appliquée à la roue (degrés) */
    public int $wheelRotation = 0;

    protected function afterParticipantsChanged(): void
    {
        $this->result = null;
    }

    // Pas de #[\Override] : redéfinit une méthode de trait (HandlesDraw), ce qui déclenche un Fatal Error en PHP 8.3+
    protected function drawType(): DrawType
    {
        return DrawType::WEIGHTED;
    }

    public function draw(RunDrawAction $action): void
    {
        $this->error = null;

        if (count($this->participants) < self::MIN_PARTICIPANTS) {
            $this->error = sprintf('Ajoutez au moins %d participants.', self::MIN_PARTICIPANTS);

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

        $rotation = WheelSegmentBuilder::cumulativeRotationFor(
            targetIndex: $index,
            total: count($this->participants),
            currentRotation: $this->wheelRotation,
            weights: $this->participantWeights,
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
        return WheelSegmentBuilder::build(
            $this->participants,
            weights: $this->participantWeights
        );
    }

    public function showLabelsOnWheel(): bool
    {
        return count($this->participants) <= self::MAX_LABELS_ON_WHEEL;
    }

    public function render()
    {
        $mode = DrawModeType::WEIGHTED->toDto();

        return view('livewire.draw.weighted-wheel-page')
            ->layout('layouts.app', [
                'title' => $mode->metaTitle,
                'metaDescription' => $mode->metaDescription,
            ]);
    }
}
