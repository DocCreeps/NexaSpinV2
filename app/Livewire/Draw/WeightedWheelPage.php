<?php

namespace App\Livewire\Draw;

use App\Application\Draw\Actions\RunDrawAction;
use App\Application\Draw\Support\WheelSegmentBuilder;
use App\Domain\Draw\Enums\DrawType;
use App\Livewire\Draw\Concerns\HandlesDraw;
use App\Livewire\Draw\Concerns\ManagesParticipants;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * Page de tirage pondéré : chaque participant a une probabilité de gagner
 * proportionnelle à son poids plutôt qu'une chance strictement égale.
 *
 * Limite connue : les segments visuels de la roue restent de taille égale
 * (WheelSegmentBuilder n'a pas encore de variante pondérée) — seule la
 * probabilité de tirage réelle (côté Domain) tient compte du poids.
 */
class WeightedWheelPage extends Component
{
    use HandlesDraw;
    use ManagesParticipants;

    private const MAX_LABELS_ON_WHEEL = 10;

    /**
     * Nombre minimal de participants requis pour lancer un tirage pondéré.
     */
    private const MIN_PARTICIPANTS = 3;

    public ?string $result = null;

    protected function afterParticipantsChanged(): void
    {
        $this->result = null;
    }

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

        $this->dispatch(
            'wheel-spin',
            rotation: WheelSegmentBuilder::rotationFor(
                $index,
                count($this->participants)
            )
        );
    }

    /**
     * Indique si le nombre minimal de participants est atteint pour activer le bouton de tirage.
     */
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
            $this->participants
        );
    }

    public function showLabelsOnWheel(): bool
    {
        return count($this->participants) <= self::MAX_LABELS_ON_WHEEL;
    }

    public function render()
    {
        return view('livewire.draw.weighted-wheel-page')
            ->layout('layouts.app');
    }
}
