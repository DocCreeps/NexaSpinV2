<?php

namespace App\Livewire\CoinFlip;

use App\Application\CoinFlip\Actions\FlipCoinAction;
use App\Application\Draw\Enums\DrawModeType;
use App\Domain\CoinFlip\Enums\CoinSide;
use App\Domain\CoinFlip\ValueObjects\CoinFlipBet;
use App\Domain\CoinFlip\ValueObjects\CoinFlipResult;
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

    public ?string $bet = null;
    public ?bool $lastBetWon = null;
    public array $betHistory = [];

    /** Libellés personnalisables des faces (la logique utilise CoinSide::value) */
    public string $pileLabel = 'Pile';
    public string $faceLabel = 'Face';

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

    /**
     * Retourne le libellé personnalisé d'une face pour l'affichage dans la vue.
     */
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

        $result = $this->performFlip($action);
        $this->result = $result->side->value;

        $this->evaluateBet($result);

        $this->dispatch('coin-flip', face: $this->result);
    }

    public function flipMultiple(FlipCoinAction $action): void
    {
        $this->error = null;
        $this->bet = null;
        $this->lastBetWon = null;

        if ($this->autoFlipCount < self::MIN_AUTO_FLIPS || $this->autoFlipCount > self::MAX_AUTO_FLIPS) {
            $this->error = sprintf(
                'Le nombre de tirages automatiques doit être compris entre %d et %d.',
                self::MIN_AUTO_FLIPS,
                self::MAX_AUTO_FLIPS
            );

            return;
        }

        for ($i = 0; $i < $this->autoFlipCount; $i++) {
            $result = $this->performFlip($action);
            $this->result = $result->side->value;
        }

        $this->dispatch('coin-flip', face: $this->result);
    }

    public function resetHistory(): void
    {
        $this->result = null;
        $this->history = [];
        $this->error = null;
        $this->bet = null;
        $this->lastBetWon = null;
        $this->betHistory = [];

        $this->dispatch('coin-flip-reset');
    }

    public function totalFlips(): int
    {
        return count($this->history);
    }

    public function pileCount(): int
    {
        return count(array_filter(
            $this->history,
            fn(string $side) => $side === CoinSide::PILE->value
        ));
    }

    public function faceCount(): int
    {
        return count(array_filter(
            $this->history,
            fn(string $side) => $side === CoinSide::FACE->value
        ));
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

    private function performFlip(FlipCoinAction $action): CoinFlipResult
    {
        $result = $action->execute();

        $this->history[] = $result->side->value;

        if (count($this->history) > self::MAX_HISTORY) {
            $this->history = array_slice($this->history, -self::MAX_HISTORY);
        }

        return $result;
    }

    private function evaluateBet(CoinFlipResult $result): void
    {
        if ($this->bet === null) {
            return;
        }

        $bet = new CoinFlipBet(CoinSide::from($this->bet), $result);

        $this->lastBetWon = $bet->won();
        $this->betHistory[] = $this->lastBetWon;

        if (count($this->betHistory) > self::MAX_HISTORY) {
            $this->betHistory = array_slice($this->betHistory, -self::MAX_HISTORY);
        }
    }

    public function render()
    {
        $mode = DrawModeType::COIN_FLIP->toDto();

        return view('livewire.coin-flip.coin-flip-page')
            ->layout('layouts.app', [
                'title' => $mode->metaTitle,
                'metaDescription' => $mode->metaDescription,
            ]);
    }
}
