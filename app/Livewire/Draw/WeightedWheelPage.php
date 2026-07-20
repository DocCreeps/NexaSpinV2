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
 * Page de tirage pondéré : chaque participant a une probabilité de gagner
 * proportionnelle à son poids plutôt qu'une chance strictement égale.
 * Les segments visuels de la roue (WheelSegmentBuilder::build/rotationFor)
 * reflètent également ce poids : un participant à 100 occupe une part
 * bien plus large qu'un participant à 1.
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

    // Note : pas de #[\Override] ici — cet attribut ne valide que les
    // méthodes héritées d'une classe mère ou d'une interface, pas celles
    // fournies par un trait `use`d (HandlesDraw). Un #[\Override] sur une
    // méthode qui ne fait que redéfinir une méthode de trait provoque un
    // Fatal error à la compilation en PHP 8.3+.
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
                count($this->participants),
                weights: $this->participantWeights
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
