<?php

namespace App\Livewire\CoinFlip;

use App\Application\CoinFlip\Actions\FlipCoinAction;
use App\Application\History\HistoryStore;
use App\Application\Home\Enums\GameModeType;
use App\Domain\CoinFlip\Enums\CoinSide;
use App\Domain\CoinFlip\ValueObjects\CoinFlipBet;
use App\Domain\CoinFlip\ValueObjects\CoinFlipResult;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Composant de pile ou face (tirage unique, multiple et gestion des paris).
 */
class CoinFlipPage extends Component
{
    private const SIDES = ['Pile', 'Face'];
    private const MAX_HISTORY = 5000;
    private const MIN_AUTO_FLIPS = 2;
    private const MAX_AUTO_FLIPS = 500;
    private const MAX_LABEL_LENGTH = 16;

    public ?string $result = null;
    public array $history = [];
    public ?string $error = null;

    /** Si > 1, bascule automatiquement en tirage multiple */
    public int $autoFlipCount = 1;

    #[Locked]
    public ?string $bet = null;
    public ?bool $lastBetWon = null;
    public array $betHistory = [];

    /** Libellés personnalisables des faces */
    public string $pileLabel = 'Pile';
    public string $faceLabel = 'Face';

    /**
     * Entrées en attente de confirmation.
     * Contient un tableau associatif avec le type ('single' ou 'multiple')
     */
    #[Locked]
    public array $pendingHistoryEntries = [];

    public function mount(HistoryStore $historyStore): void
    {
        foreach ($historyStore->all(GameModeType::COIN_FLIP) as $entry) {
            $this->history[] = $entry;

            if (($entry['type'] ?? 'single') === 'single' && isset($entry['bet_won']) && $entry['bet_won'] !== null) {
                $this->betHistory[] = $entry['bet_won'];
            }
        }
    }

    public function launch(FlipCoinAction $action): void
    {
        if ($this->autoFlipCount > 1) {
            $this->flipMultiple($action);
        } else {
            $this->flip($action);
        }
    }

    public function selectBet(string $side): void
    {
        if (! in_array($side, ['pile', 'face'], true)) {
            return;
        }

        $this->bet = $this->bet === $side ? null : $side;
    }

    public function label(string $side): string
    {
        return $side === CoinSide::PILE->value ? $this->pileLabel : $this->faceLabel;
    }

    public function updatedPileLabel(): void
    {
        $this->pileLabel = $this->sanitizeLabel($this->pileLabel, 'Pile');
    }

    public function updatedFaceLabel(): void
    {
        $this->faceLabel = $this->sanitizeLabel($this->faceLabel, 'Face');
    }

    public function resetLabels(): void
    {
        $this->pileLabel = 'Pile';
        $this->faceLabel = 'Face';
    }

    private function sanitizeLabel(string $value, string $default): string
    {
        $value = trim($value);

        if ($value === '') {
            return $default;
        }

        return mb_substr($value, 0, self::MAX_LABEL_LENGTH);
    }

    public function flip(FlipCoinAction $action): void
    {
        $this->error = null;
        $this->lastBetWon = null;
        $this->pendingHistoryEntries = [];

        $result = $action->execute();
        $this->result = $result->side->value;

        $this->evaluateBet($result);

        $this->pendingHistoryEntries[] = [
            'type' => 'single',
            'side' => $result->side->value,
            'side_label' => $this->label($result->side->value),
            'bet' => $this->bet,
            'bet_label' => $this->bet ? $this->label($this->bet) : null,
            'bet_won' => $this->lastBetWon,
        ];

        $this->dispatch('coin-flip', face: $this->result);
    }

    public function flipMultiple(FlipCoinAction $action): void
    {
        $this->error = null;
        $this->bet = null;
        $this->lastBetWon = null;
        $this->pendingHistoryEntries = [];

        if ($this->autoFlipCount < self::MIN_AUTO_FLIPS || $this->autoFlipCount > self::MAX_AUTO_FLIPS) {
            $this->error = sprintf(
                'Le nombre de tirages automatiques doit être compris entre %d et %d.',
                self::MIN_AUTO_FLIPS,
                self::MAX_AUTO_FLIPS
            );

            return;
        }

        $pileCount = 0;
        $faceCount = 0;

        for ($i = 0; $i < $this->autoFlipCount; $i++) {
            $result = $action->execute();
            $this->result = $result->side->value;

            if ($result->side->value === CoinSide::PILE->value) {
                $pileCount++;
            } else {
                $faceCount++;
            }
        }

        $winner = null;
        if ($pileCount > $faceCount) {
            $winner = CoinSide::PILE->value;
        } elseif ($faceCount > $pileCount) {
            $winner = CoinSide::FACE->value;
        } // Si égalité, $winner reste null

        $this->pendingHistoryEntries[] = [
            'type' => 'multiple',
            'count' => $this->autoFlipCount,
            'pile_count' => $pileCount,
            'face_count' => $faceCount,
            'pile_label' => $this->pileLabel,
            'face_label' => $this->faceLabel,
            'winner' => $winner,
            'winner_label' => $winner ? $this->label($winner) : 'Égalité',
        ];

        $this->dispatch('coin-flip', face: $this->result);
    }

    public function confirmFlip(): void
    {
        if ($this->pendingHistoryEntries === []) {
            return;
        }

        $store = app(HistoryStore::class);

        foreach ($this->pendingHistoryEntries as $entry) {
            $this->history[] = $entry;

            if (($entry['type'] ?? 'single') === 'single' && isset($entry['bet_won']) && $entry['bet_won'] !== null) {
                $this->betHistory[] = $entry['bet_won'];
            }

            $store->push(GameModeType::COIN_FLIP, $entry);
        }

        if (count($this->history) > self::MAX_HISTORY) {
            $this->history = array_slice($this->history, -self::MAX_HISTORY);
        }

        $this->pendingHistoryEntries = [];
    }

    public function resetHistory(): void
    {
        $this->result = null;
        $this->history = [];
        $this->error = null;
        $this->bet = null;
        $this->lastBetWon = null;
        $this->betHistory = [];
        $this->pendingHistoryEntries = [];

        app(HistoryStore::class)->clear(GameModeType::COIN_FLIP);

        $this->dispatch('coin-flip-reset');
    }

    public function totalFlips(): int
    {
        $total = 0;
        foreach ($this->history as $entry) {
            $total += ($entry['type'] ?? 'single') === 'multiple' ? $entry['count'] : 1;
        }

        return $total;
    }

    public function pileCount(): int
    {
        $total = 0;
        foreach ($this->history as $entry) {
            if (($entry['type'] ?? 'single') === 'multiple') {
                $total += $entry['pile_count'];
            } elseif (($entry['side'] ?? null) === CoinSide::PILE->value) {
                $total++;
            }
        }

        return $total;
    }

    public function faceCount(): int
    {
        $total = 0;
        foreach ($this->history as $entry) {
            if (($entry['type'] ?? 'single') === 'multiple') {
                $total += $entry['face_count'];
            } elseif (($entry['side'] ?? null) === CoinSide::FACE->value) {
                $total++;
            }
        }

        return $total;
    }

    public function betWinCount(): int
    {
        return count(array_filter($this->betHistory, fn(bool $won) => $won));
    }

    public function betLossCount(): int
    {
        return count(array_filter($this->betHistory, fn(bool $won) => ! $won));
    }

    public function betTotal(): int
    {
        return count($this->betHistory);
    }

    private function evaluateBet(CoinFlipResult $result): void
    {
        if ($this->bet === null) {
            return;
        }

        $chosenSide = CoinSide::tryFrom($this->bet);

        if ($chosenSide === null) {
            $this->bet = null;

            return;
        }

        $bet = new CoinFlipBet($chosenSide, $result);

        $this->lastBetWon = $bet->won();
    }

    public function render()
    {
        $mode = GameModeType::COIN_FLIP->toDto();

        return view('livewire.coin-flip.coin-flip-page')
            ->layout('layouts.app', [
                'title' => $mode->metaTitle,
                'metaDescription' => $mode->metaDescription,
            ]);
    }
}
