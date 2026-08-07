<?php

namespace App\Livewire\Draw;

use App\Application\Draw\Actions\RunDrawAction;
use App\Application\History\HistoryStore;
use App\Application\Home\Enums\GameModeType;
use App\Application\Draw\Support\WheelSegmentBuilder;
use App\Domain\Draw\Enums\DrawType;
use App\Livewire\Draw\Concerns\HandlesDraw;
use App\Livewire\Draw\Concerns\ManagesParticipants;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
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
    private const MAX_HISTORY = 100;

    public ?string $result = null;

    /** Rotation cumulée appliquée à la roue (degrés) */
    public int $wheelRotation = 0;

    /** @var array<int, array{winner: string, participants: array<int, string>, weights: array<string, int>}> */
    public array $history = [];

    /**
     * Tirage en attente de confirmation (voir confirmDraw()) : le gagnant est
     * déterminé immédiatement (nécessaire pour faire tourner la roue jusqu'à lui),
     * mais n'atterrit dans $history/le cache qu'une fois l'animation terminée
     * côté client, pour ne pas spoiler le résultat avant la fin du tour de roue.
     *
     * @var array{winner: string, participants: array<int, string>, weights: array<string, int>}|null
     */
    #[Locked]
    public ?array $pendingHistoryEntry = null;

    public function mount(HistoryStore $historyStore): void
    {
        $this->history = array_map(
            static fn(array $entry) => [
                'winner' => $entry['winner'],
                'participants' => $entry['participants'],
                'weights' => $entry['weights'],
            ],
            $historyStore->all(GameModeType::WEIGHTED)
        );
    }

    public function clearHistory(): void
    {
        $this->history = [];

        app(HistoryStore::class)->clear(GameModeType::WEIGHTED);
    }

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
        $this->pendingHistoryEntry = null;

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

        $weights = array_combine($this->participants, $this->participantWeights);

        $this->pendingHistoryEntry = [
            'winner' => $winner->name,
            'participants' => $this->participants,
            'weights' => $weights,
        ];

        $rotation = WheelSegmentBuilder::cumulativeRotationFor(
            targetIndex: $index,
            total: count($this->participants),
            currentRotation: $this->wheelRotation,
            weights: $this->participantWeights,
        );

        $this->wheelRotation = $rotation['newRotation'];

        $this->dispatch('wheel-spin', rotation: $rotation['delta']);
    }

    /**
     * Confirme le tirage en attente : appelé côté client une fois l'animation
     * de la roue terminée (+ un court délai), pour que l'historique n'apparaisse
     * pas avant que le résultat ne soit visuellement révélé.
     */
    public function confirmDraw(): void
    {
        if ($this->pendingHistoryEntry === null) {
            return;
        }

        $this->history[] = $this->pendingHistoryEntry;

        if (count($this->history) > self::MAX_HISTORY) {
            $this->history = array_slice($this->history, -self::MAX_HISTORY);
        }

        app(HistoryStore::class)->push(GameModeType::WEIGHTED, $this->pendingHistoryEntry);

        $this->pendingHistoryEntry = null;
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
        $mode = GameModeType::WEIGHTED->toDto();

        return view('livewire.draw.weighted-wheel-page')
            ->layout('layouts.app', [
                'title' => $mode->metaTitle,
                'metaDescription' => $mode->metaDescription,
            ]);
    }
}
