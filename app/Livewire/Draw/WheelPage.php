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

class WheelPage extends Component
{
    use ManagesParticipants;

    /**
     * Au-delà de ce nombre de participants, les noms ne sont plus affichés
     * directement sur la roue (parts trop fines) : on bascule sur une
     * légende à côté.
     */
    private const MAX_LABELS_ON_WHEEL = 10;

    public ?string $result = null;

    protected function afterParticipantsChanged(): void
    {
        $this->result = null;
    }

    public function draw(RunDrawAction $action): void
    {
        $this->error = null;
        $this->result = null;

        if (count($this->participants) < 2) {
            $this->error = 'Ajoutez au moins deux participants avant de lancer le tirage.';

            return;
        }

        $winner = $action->execute(
            new DrawData(
                participants: array_map(
                    fn (string $name) => new Participant($name),
                    $this->participants,
                ),
                type: DrawType::WHEEL,
            )
        );

        if ($winner === null) {
            return;
        }

        $winnerIndex = array_search($winner->name, $this->participants, true);

        $this->result = $winner->name;

        $this->dispatch(
            'wheel-spin',
            rotation: WheelSegmentBuilder::rotationFor((int) $winnerIndex, count($this->participants)),
        );
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
        return view('livewire.draw.wheel-page')
            ->layout('layouts.app');
    }
}
