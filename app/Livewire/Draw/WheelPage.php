<?php

namespace App\Livewire\Draw;

use App\Application\Draw\Actions\RunDrawAction;
use App\Application\History\HistoryStore;
use App\Application\Home\Enums\GameModeType;
use App\Application\Draw\Support\WheelSegmentBuilder;
use App\Livewire\Draw\Concerns\HandlesDraw;
use App\Livewire\Draw\Concerns\ManagesParticipants;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Page de tirage classique sous forme de Roue de la Fortune.
 */
class WheelPage extends Component
{
    use HandlesDraw;
    use ManagesParticipants;

    private const MAX_LABELS_ON_WHEEL = 10;
    private const MIN_PARTICIPANTS = 3;
    private const MAX_HISTORY = 100;

    public ?string $result = null;

    /** Rotation cumulée appliquée à la roue (degrés) */
    public int $wheelRotation = 0;

    /** @var array<int, array{winner: string, participants: array<int, string>}> */
    public array $history = [];

    /**
     * Tirage en attente de confirmation (voir confirmDraw()) : le gagnant est
     * déterminé immédiatement (nécessaire pour faire tourner la roue jusqu'à lui),
     * mais n'atterrit dans $history/le cache qu'une fois l'animation terminée
     * côté client, pour ne pas spoiler le résultat avant la fin du tour de roue.
     *
     * @var array{winner: string, participants: array<int, string>}|null
     */
    #[Locked]
    public ?array $pendingHistoryEntry = null;

    public function mount(HistoryStore $historyStore): void
    {
        $this->history = array_map(
            static fn(array $entry) => [
                'winner' => $entry['winner'],
                'participants' => $entry['participants'],
            ],
            $historyStore->all(GameModeType::CLASSIC)
        );
    }

    public function clearHistory(): void
    {
        $this->history = [];

        app(HistoryStore::class)->clear(GameModeType::CLASSIC);
    }

    protected function afterParticipantsChanged(): void
    {
        $this->result = null;
    }

    public function draw(RunDrawAction $action): void
    {
        $this->error = null;
        $this->pendingHistoryEntry = null;

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

        $this->pendingHistoryEntry = [
            'winner' => $winner->name,
            'participants' => $this->participants,
        ];

        // On calcule un delta pour Alpine (x-draw.wheel) qui additionne la rotation au lieu d'un angle absolu
        $rotation = WheelSegmentBuilder::cumulativeRotationFor(
            targetIndex: $index,
            total: count($this->participants),
            currentRotation: $this->wheelRotation,
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

        app(HistoryStore::class)->push(GameModeType::CLASSIC, $this->pendingHistoryEntry);

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
        return WheelSegmentBuilder::build($this->participants);
    }

    public function showLabelsOnWheel(): bool
    {
        return count($this->participants) <= self::MAX_LABELS_ON_WHEEL;
    }

    public function render()
    {
        $mode = GameModeType::CLASSIC->toDto();

        return view('livewire.draw.wheel-page')
            ->layout('layouts.app', [
                'title' => $mode->metaTitle,
                'metaDescription' => $mode->metaDescription,
            ]);
    }
}
