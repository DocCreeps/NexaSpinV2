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

    /**
     * #[Locked] : $bet ne doit jamais être modifiable directement depuis un payload
     * Livewire côté client (contrairement à un wire:model classique). Seule la méthode
     * serveur selectBet() — qui valide la valeur — peut la faire évoluer. Sans ce verrou,
     * un payload AJAX forgé pourrait fixer $bet à une valeur arbitraire et faire échouer
     * CoinSide::from() dans evaluateBet() avec une erreur serveur non gérée.
     */
    #[Locked]
    public ?string $bet = null;
    public ?bool $lastBetWon = null;
    public array $betHistory = [];

    /** Libellés personnalisables des faces (la logique utilise CoinSide::value) */
    public string $pileLabel = 'Pile';
    public string $faceLabel = 'Face';

    /**
     * Résultat(s) en attente de confirmation (voir confirmFlip()) : le tirage est
     * calculé immédiatement (nécessaire pour l'animation de la pièce), mais
     * n'atterrit dans $history/$betHistory/le cache qu'une fois l'animation
     * terminée côté client, pour ne pas spoiler le résultat avant la fin du flip.
     *
     * @var array<int, array{side: string, bet: ?string, bet_won: ?bool}>
     */
    #[Locked]
    public array $pendingHistoryEntries = [];

    /**
     * Réhydrate le résumé rapide depuis l'historique en cache (voir HistoryStore),
     * pour qu'il survive à un rechargement de page.
     */
    public function mount(HistoryStore $historyStore): void
    {
        foreach ($historyStore->all(GameModeType::COIN_FLIP) as $entry) {
            $this->history[] = $entry['side'];

            if ($entry['bet_won'] !== null) {
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
        $this->pendingHistoryEntries = [];

        $result = $action->execute();
        $this->result = $result->side->value;

        $this->evaluateBet($result);

        $this->pendingHistoryEntries[] = [
            'side' => $result->side->value,
            'bet' => $this->bet,
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

        for ($i = 0; $i < $this->autoFlipCount; $i++) {
            $result = $action->execute();
            $this->result = $result->side->value;

            $this->pendingHistoryEntries[] = [
                'side' => $result->side->value,
                'bet' => null,
                'bet_won' => null,
            ];
        }

        $this->dispatch('coin-flip', face: $this->result);
    }

    /**
     * Confirme le(s) tirage(s) en attente : appelé côté client une fois l'animation
     * de la pièce terminée (+ un court délai), pour que l'historique n'apparaisse
     * pas avant que le résultat ne soit visuellement révélé.
     */
    public function confirmFlip(): void
    {
        if ($this->pendingHistoryEntries === []) {
            return;
        }

        $store = app(HistoryStore::class);

        foreach ($this->pendingHistoryEntries as $entry) {
            $this->history[] = $entry['side'];

            if ($entry['bet_won'] !== null) {
                $this->betHistory[] = $entry['bet_won'];
            }

            $store->push(GameModeType::COIN_FLIP, $entry);
        }

        if (count($this->history) > self::MAX_HISTORY) {
            $this->history = array_slice($this->history, -self::MAX_HISTORY);
        }

        if (count($this->betHistory) > self::MAX_HISTORY) {
            $this->betHistory = array_slice($this->betHistory, -self::MAX_HISTORY);
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

    private function evaluateBet(CoinFlipResult $result): void
    {
        if ($this->bet === null) {
            return;
        }

        $chosenSide = CoinSide::tryFrom($this->bet);

        // Défense en profondeur : si $bet ne correspond à aucune face valide
        // (ne devrait jamais arriver grâce à #[Locked] + selectBet()), on ignore
        // le pari plutôt que de laisser planter la requête avec un \ValueError.
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
