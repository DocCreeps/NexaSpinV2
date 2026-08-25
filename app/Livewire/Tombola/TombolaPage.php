<?php

namespace App\Livewire\Tombola;

use App\Application\Draw\Actions\RunDrawAction;
use App\Application\Draw\DTOs\DrawData;
use App\Application\History\HistoryStore;
use App\Application\Home\Enums\GameModeType;
use App\Domain\Draw\Enums\DrawDisplay;
use App\Domain\Draw\Enums\DrawType;
use App\Livewire\Draw\Concerns\ManagesParticipants;
use Livewire\Component;

class TombolaPage extends Component
{
    use ManagesParticipants;

    private const MIN_PARTICIPANTS = 3;
    private const MAX_HISTORY = 50;

    public string $drawMode = 'fixed';
    public int $lotsCount = 3;
    public bool $allowDuplicates = false;
    public bool $slowMode = true;
    public bool $autoAdvance = true;

    public array $remainingPool = [];
    public array $remainingWeights = [];

    /** Index du lot actuel pour le tirage lent progressif. */
    public int $currentLotIndex = 0;

    /** @var array<int, string> */
    public array $winners = [];

    public bool $drawing = false;

    /** @var array<int, array{winners: array<int,string>, participants: array<int,string>, weights: array<int,int>}> */
    public array $history = [];

    public function mount(HistoryStore $historyStore): void
    {
        $this->history = $historyStore->all(GameModeType::TOMBOLA);
    }

    public function clearHistory(): void
    {
        $this->history = [];
        app(HistoryStore::class)->clear(GameModeType::TOMBOLA);
    }

    protected function afterParticipantsChanged(): void
    {
        $this->resetDraw();
    }

    protected function participantsAreLocked(): bool
    {
        return $this->drawing;
    }

    public function setDrawMode(string $drawMode): void
    {
        if (! in_array($drawMode, ['fixed', 'one_by_one'], true)) {
            return;
        }

        $this->drawMode = $drawMode;
        $this->resetDraw();
    }

    public function toggleAllowDuplicates(): void
    {
        if ($this->drawing) {
            return;
        }

        $this->allowDuplicates = ! $this->allowDuplicates;
        $this->resetDraw();
    }

    public function toggleSlowMode(): void
    {
        if ($this->drawing) {
            return;
        }

        $this->slowMode = ! $this->slowMode;
    }

    public function incrementLotsCount(): void
    {
        $maxAllowed = ($this->allowDuplicates || count($this->participants) === 0)
            ? 100
            : count($this->participants);

        $this->lotsCount = min($this->lotsCount + 1, $maxAllowed);
    }

    public function decrementLotsCount(): void
    {
        $this->lotsCount = max($this->lotsCount - 1, 1);
    }

    public function newDraw(): void
    {
        $this->resetDraw();
    }

    private function resetDraw(): void
    {
        $this->drawing = false;
        $this->winners = [];
        $this->remainingPool = [];
        $this->remainingWeights = [];
        $this->currentLotIndex = 0;
    }

    public function start(): void
    {
        $this->error = null;

        if (count($this->participants) < self::MIN_PARTICIPANTS) {
            $this->error = sprintf('Ajoutez au moins %d participants.', self::MIN_PARTICIPANTS);
            return;
        }

        if ($this->drawMode === 'fixed') {
            if ($this->lotsCount < 1) {
                $this->error = 'Le nombre de lots doit être d’au moins 1.';
                return;
            }

            if (! $this->allowDuplicates && $this->lotsCount > count($this->participants)) {
                $this->error = sprintf('Sans remise, le nombre de lots ne peut pas dépasser le nombre de participants (%d).', count($this->participants));
                return;
            }
        }

        $this->winners = [];
        $this->remainingPool = array_values($this->participants);
        $this->remainingWeights = array_values($this->participantWeights);
        $this->drawing = true;
        $this->currentLotIndex = 0;

        // Tirage instantané ou premier pas du tirage progressif
        if ($this->drawMode === 'fixed') {
            if ($this->slowMode) {
                $this->drawNextStep();
            } else {
                for ($i = 0; $i < $this->lotsCount; $i++) {
                    if ($this->remainingPool === []) {
                        break;
                    }
                    $this->drawOne();
                }
                $this->finish();
            }
        }
    }

    /** Appelé périodiquement en JS / wire:poll lorsque le mode suspense est actif. */
    public function drawNextStep(): void
    {
        if (! $this->drawing) {
            return;
        }

        if ($this->remainingPool !== [] && $this->currentLotIndex < $this->lotsCount) {
            $this->drawOne();
            $this->currentLotIndex++;
        }

        if ($this->remainingPool === [] || $this->currentLotIndex >= $this->lotsCount) {
            $this->finish();
        }
    }

    public function drawNext(): void
    {
        if (! $this->drawing || $this->remainingPool === []) {
            return;
        }

        $this->drawOne();

        if ($this->remainingPool === []) {
            $this->finish();
        }
    }

    public function stop(): void
    {
        if (! $this->drawing || $this->winners === []) {
            return;
        }

        $this->finish();
    }

    private function drawOne(): void
    {
        if ($this->remainingPool === []) {
            return;
        }

        if (count($this->remainingPool) < 3) {
            $index = $this->getRandomWeightedIndex($this->remainingWeights);
            $winnerName = $this->remainingPool[$index] ?? null;
        } else {
            $result = app(RunDrawAction::class)->execute(new DrawData(
                participants: $this->remainingPool,
                type: DrawType::WEIGHTED,
                display: DrawDisplay::WHEEL,
                weights: $this->remainingWeights,
            ));

            $winnerName = $result->winner->name;
            $index = array_search($winnerName, $this->remainingPool, true);
        }

        if ($winnerName !== null && $index !== false) {
            $this->winners[] = $winnerName;

            if ($this->allowDuplicates) {
                $this->remainingWeights[$index]--;

                if ($this->remainingWeights[$index] <= 0) {
                    unset($this->remainingPool[$index], $this->remainingWeights[$index]);

                    $this->remainingPool = array_values($this->remainingPool);
                    $this->remainingWeights = array_values($this->remainingWeights);
                }
            } else {
                unset($this->remainingPool[$index], $this->remainingWeights[$index]);

                $this->remainingPool = array_values($this->remainingPool);
                $this->remainingWeights = array_values($this->remainingWeights);
            }
        }
    }

    private function getRandomWeightedIndex(array $weights): int
    {
        $totalWeight = array_sum($weights);
        if ($totalWeight <= 0) {
            return 0;
        }

        $random = random_int(1, $totalWeight);
        $current = 0;

        foreach ($weights as $index => $weight) {
            $current += $weight;
            if ($random <= $current) {
                return $index;
            }
        }

        return 0;
    }

    private function finish(): void
    {
        $this->drawing = false;

        $entry = [
            'winners' => $this->winners,
            'participants' => array_values($this->participants),
            'weights' => array_values($this->participantWeights),
        ];

        $this->history[] = $entry;

        if (count($this->history) > self::MAX_HISTORY) {
            $this->history = array_slice($this->history, -self::MAX_HISTORY);
        }

        app(HistoryStore::class)->push(GameModeType::TOMBOLA, $entry);
    }

    public function canStart(): bool
    {
        return ! $this->drawing && count($this->participants) >= self::MIN_PARTICIPANTS;
    }

    public function render()
    {
        $mode = GameModeType::TOMBOLA->toDto();

        return view('livewire.tombola.tombola-page')
            ->layout('layouts.app', [
                'title' => $mode->metaTitle,
                'metaDescription' => $mode->metaDescription,
            ]);
    }
}
