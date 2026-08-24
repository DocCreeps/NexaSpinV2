<?php

namespace App\Livewire\Roulette;

use App\Application\History\HistoryStore;
use App\Application\Home\Enums\GameModeType;
use App\Application\Roulette\BankrollStore;
use App\Application\Roulette\RouletteBetEvaluator;
use App\Domain\Roulette\Enums\RouletteBetType;
use App\Domain\Roulette\RoulettePocket;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Page de la roulette numérique : roulette américaine (0, 00, 1-36) avec mises
 * casino (plein, chances simples, douzaines, colonnes) et cagnotte persistante.
 */
class NumberRoulettePage extends Component
{
    private const MIN_STAKE = 1;
    private const MAX_HISTORY = 100;

    public string $betType = 'red';
    public ?string $betNumber = null;
    public int $stake = 50;

    public int $bankroll = 0;
    public int $startingBankroll = 0;

    public ?string $error = null;

    public ?string $lastResult = null;
    public ?string $lastColor = null;
    public ?bool $lastWin = null;
    public ?int $lastPayout = null;

    public bool $spinning = false;

    /**
     * @var array<int, array{bet_type: string, bet_type_label: string, bet_number: ?string, stake: int, result: string, color: string, won: bool, payout: int, bankroll_after: int}>
     */
    public array $history = [];

    /**
     * Tirage en attente de confirmation (voir confirmSpin()) : le résultat est
     * déterminé immédiatement, mais n'atterrit dans l'historique/la cagnotte
     * affichée qu'une fois l'animation terminée côté client.
     *
     * @var array<string, mixed>|null
     */
    #[Locked]
    public ?array $pendingHistoryEntry = null;

    public function mount(HistoryStore $historyStore, BankrollStore $bankrollStore): void
    {
        $this->bankroll = $bankrollStore->get();
        $this->startingBankroll = $bankrollStore->startingAmount();
        $this->history = $historyStore->all(GameModeType::NUMBER_ROULETTE, self::MAX_HISTORY);
    }

    public function clearHistory(): void
    {
        $this->history = [];

        app(HistoryStore::class)->clear(GameModeType::NUMBER_ROULETTE);
    }

    public function resetBankroll(BankrollStore $bankrollStore): void
    {
        $bankrollStore->reset();
        $this->bankroll = $bankrollStore->startingAmount();
        $this->lastResult = null;
        $this->lastWin = null;
        $this->lastPayout = null;
    }

    public function selectBetType(string $betType): void
    {
        if (RouletteBetType::tryFrom($betType) === null || $this->spinning) {
            return;
        }

        $this->betType = $betType;
        $this->error = null;

        if ($betType !== RouletteBetType::STRAIGHT->value) {
            $this->betNumber = null;
        }
    }

    public function selectNumber(string $number): void
    {
        if ($this->spinning) {
            return;
        }

        $this->betType = RouletteBetType::STRAIGHT->value;
        $this->betNumber = $number;
        $this->error = null;
    }

    public function spin(RouletteBetEvaluator $evaluator, BankrollStore $bankrollStore): void
    {
        $this->error = null;

        if ($this->spinning) {
            return;
        }

        $betType = RouletteBetType::tryFrom($this->betType);

        if ($betType === null) {
            $this->error = 'Choisissez un type de mise.';

            return;
        }

        if ($betType->requiresNumber() && ($this->betNumber === null || $this->betNumber === '')) {
            $this->error = 'Choisissez un numéro pour un pari plein.';

            return;
        }

        if ($this->stake < self::MIN_STAKE) {
            $this->error = sprintf('La mise doit être d’au moins %d.', self::MIN_STAKE);

            return;
        }

        if ($this->stake > $this->bankroll) {
            $this->error = 'Votre cagnotte est insuffisante pour cette mise.';

            return;
        }

        $result = RoulettePocket::random();
        $won = $evaluator->isWinning($betType, $this->betNumber, $result);
        $payout = $won ? $this->stake * $betType->payoutMultiplier() : -$this->stake;
        $newBankroll = max($this->bankroll + $payout, 0);

        $this->pendingHistoryEntry = [
            'bet_type' => $betType->value,
            'bet_type_label' => $betType->label(),
            'bet_number' => $this->betNumber,
            'stake' => $this->stake,
            'result' => $result,
            'color' => RoulettePocket::color($result),
            'won' => $won,
            'payout' => $payout,
            'bankroll_after' => $newBankroll,
        ];

        $this->bankroll = $newBankroll;
        $bankrollStore->set($newBankroll);
        $this->spinning = true;

        $this->dispatch('roulette-spin', result: $result);
    }

    /**
     * Confirme le tirage en attente : appelé côté client une fois l'animation
     * de la roue terminée (+ un court délai), même principe que les roues
     * classiques pour ne pas spoiler le résultat avant la fin de l'animation.
     */
    public function confirmSpin(): void
    {
        $this->spinning = false;

        if ($this->pendingHistoryEntry === null) {
            return;
        }

        $entry = $this->pendingHistoryEntry;

        $this->lastResult = $entry['result'];
        $this->lastColor = $entry['color'];
        $this->lastWin = $entry['won'];
        $this->lastPayout = $entry['payout'];

        $this->history[] = $entry;

        if (count($this->history) > self::MAX_HISTORY) {
            $this->history = array_slice($this->history, -self::MAX_HISTORY);
        }

        app(HistoryStore::class)->push(GameModeType::NUMBER_ROULETTE, $entry);

        $this->pendingHistoryEntry = null;
    }

    public function canSpin(): bool
    {
        return ! $this->spinning
            && $this->stake >= self::MIN_STAKE
            && $this->stake <= $this->bankroll;
    }

    /**
     * @return array<int, RouletteBetType>
     */
    public function simpleChances(): array
    {
        return RouletteBetType::simpleChances();
    }

    /**
     * @return array<int, RouletteBetType>
     */
    public function dozenBets(): array
    {
        return RouletteBetType::dozens();
    }

    /**
     * @return array<int, RouletteBetType>
     */
    public function columnBets(): array
    {
        return RouletteBetType::columns();
    }

    public function render()
    {
        $mode = GameModeType::NUMBER_ROULETTE->toDto();

        return view('livewire.roulette.number-roulette-page')
            ->layout('layouts.app', [
                'title' => $mode->metaTitle,
                'metaDescription' => $mode->metaDescription,
            ]);
    }
}
