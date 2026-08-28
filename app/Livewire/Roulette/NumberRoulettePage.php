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

class NumberRoulettePage extends Component
{
private const MIN_STAKE = 1;
private const MAX_HISTORY = 100;

public array $bets = [];

public string $selectedBetType = 'red';
public ?string $selectedBetNumber = null;
public int $stake = 50;

#[Locked]
public int $bankroll = 0;

#[Locked]
public int $startingBankroll = 0;

public ?string $error = null;
public ?string $lastResult = null;
public ?string $lastColor = null;
public ?bool $lastWin = null;
public ?int $lastPayout = null;
public bool $spinning = false;

public array $history = [];

#[Locked]
public ?array $pendingHistoryEntry = null;

public function mount(HistoryStore $historyStore, BankrollStore $bankrollStore): void
{
$this->bankroll = $bankrollStore->get();
$this->startingBankroll = $bankrollStore->startingAmount();
$this->history = $historyStore->all(GameModeType::NUMBER_ROULETTE, self::MAX_HISTORY);
}

public function selectBetType(string $betType): void
{
if (RouletteBetType::tryFrom($betType) === null || $this->spinning) {
return;
}

$this->selectedBetType = $betType;
$this->error = null;

if ($betType !== RouletteBetType::STRAIGHT->value) {
$this->selectedBetNumber = null;
}
}

public function selectNumber(string $number): void
{
if ($this->spinning) {
return;
}

$this->selectedBetType = RouletteBetType::STRAIGHT->value;
$this->selectedBetNumber = $number;
$this->error = null;
}

/**
* Ajoute le pari et déduit immédiatement le montant de la cagnotte.
*/
    public function addBet(BankrollStore $bankrollStore): void
    {
        $betType = RouletteBetType::tryFrom($this->selectedBetType);

        if ($betType === null) {
            $this->error = 'Choisissez un type de mise valide.';
            return;
        }

        if ($betType->requiresNumber() && ($this->selectedBetNumber === null || $this->selectedBetNumber === '')) {
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

        // Recherche d'un pari identique déjà présent (même type + même numéro)
        $existingIndex = null;
        foreach ($this->bets as $index => $bet) {
            if ($bet['bet_type'] === $betType->value && $bet['bet_number'] === $this->selectedBetNumber) {
                $existingIndex = $index;
                break;
            }
        }

        // Débit de la cagnotte
        $this->bankroll -= $this->stake;
        $bankrollStore->set($this->bankroll);

        // Fusion si existant, sinon ajout
        if ($existingIndex !== null) {
            $this->bets[$existingIndex]['stake'] += $this->stake;
        } else {
            $this->bets[] = [
                'id' => uniqid('bet_', true),
                'bet_type' => $betType->value,
                'bet_number' => $this->selectedBetNumber,
                'stake' => $this->stake,
            ];
        }

        $this->error = null;
    }

    /**
    * Supprime un pari et remplace le montant dans la cagnotte.
    */
    public function removeBet(int $index, BankrollStore $bankrollStore): void
    {
    if ($this->spinning || ! isset($this->bets[$index])) {
    return;
    }

    $refundAmount = $this->bets[$index]['stake'];
    array_splice($this->bets, $index, 1);

    $this->bankroll += $refundAmount;
    $bankrollStore->set($this->bankroll);
    }

    /**
    * Vide tous les paris et rembourse la cagnotte.
    */
    public function clearBets(BankrollStore $bankrollStore): void
    {
    if ($this->spinning) {
    return;
    }

    $totalRefund = array_sum(array_column($this->bets, 'stake'));
    $this->bets = [];

    $this->bankroll += $totalRefund;
    $bankrollStore->set($this->bankroll);
    }

    public function spin(RouletteBetEvaluator $evaluator): void
    {
    $this->error = null;

    if ($this->spinning) {
    return;
    }

    if (empty($this->bets)) {
    $this->error = 'Veuillez placer au moins un pari avant de lancer.';
    return;
    }

    $result = RoulettePocket::random();
    $totalStake = array_sum(array_column($this->bets, 'stake'));
    $totalReturn = 0; // Total retourné (mise + gain)
    $betsSummary = [];

    foreach ($this->bets as $bet) {
    $betType = RouletteBetType::from($bet['bet_type']);
    $won = $evaluator->isWinning($betType, $bet['bet_number'], $result);

    // Si gagné : on rend la mise originale + le gain du multiplicateur
    $returnedAmount = $won ? $bet['stake'] + ($bet['stake'] * $betType->payoutMultiplier()) : 0;
    $totalReturn += $returnedAmount;

    $betsSummary[] = [
    'type' => $betType->value,
    'label' => $betType->label(),
    'number' => $bet['bet_number'],
    'stake' => $bet['stake'],
    'won' => $won,
    'returned' => $returnedAmount,
    'net_profit' => $won ? ($bet['stake'] * $betType->payoutMultiplier()) : -$bet['stake'],
    ];
    }

    $netProfit = array_sum(array_column($betsSummary, 'net_profit'));

    // On stocke le résultat en attente de la fin de l'animation
    $this->pendingHistoryEntry = [
    'bets' => $betsSummary,
    'total_stake' => $totalStake,
    'total_return' => $totalReturn,
    'result' => $result,
    'color' => RoulettePocket::color($result),
    'won' => $netProfit > 0,
    'payout' => $netProfit,
    ];

    $this->spinning = true;
    $this->dispatch('roulette-spin', result: $result);
    }

    /**
    * Exécuté automatiquement à la fin du timer JavaScript de l'animation.
    */
    public function confirmSpin(BankrollStore $bankrollStore): void
    {
    $this->spinning = false;

    if ($this->pendingHistoryEntry === null) {
    return;
    }

    $entry = $this->pendingHistoryEntry;

    // Ajout des gains validés à la cagnotte
    if ($entry['total_return'] > 0) {
    $this->bankroll += $entry['total_return'];
    $bankrollStore->set($this->bankroll);
    }

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
    $this->bets = []; // Réinitialisation de la table pour le tour suivant
    }

    public function getTotalStakeProperty(): int
    {
    return array_sum(array_column($this->bets, 'stake'));
    }

    public function canSpin(): bool
    {
    return ! $this->spinning && count($this->bets) > 0;
    }

    public function simpleChances(): array
    {
    return RouletteBetType::simpleChances();
    }

    public function dozenBets(): array
    {
    return RouletteBetType::dozens();
    }

    public function columnBets(): array
    {
    return RouletteBetType::columns();
    }

    public function clearHistory(): void
    {
    $this->history = [];
    app(HistoryStore::class)->clear(GameModeType::NUMBER_ROULETTE);
    }

    public function resetBankroll(BankrollStore $bankrollStore): void
    {
    if ($this->spinning) {
    return;
    }

    $bankrollStore->reset();
    $this->bankroll = $bankrollStore->startingAmount();
    $this->lastResult = null;
    $this->lastWin = null;
    $this->lastPayout = null;
    $this->bets = [];
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
