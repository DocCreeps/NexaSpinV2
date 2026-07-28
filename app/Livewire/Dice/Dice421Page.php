<?php

namespace App\Livewire\Dice;

use App\Application\Dice\Actions\RollDiceAction;
use App\Application\Home\Enums\GameModeType;
use App\Domain\Dice\Contracts\DiceGameStrategy;
use App\Domain\Dice\Strategies\FourTwoOneStrategy;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Composant Livewire gérant une partie de 421.
 *
 * Maintient l'état du jeu (dés, lancers, historique) et délègue
 * la logique métier à RollDiceAction via FourTwoOneStrategy.
 */
class Dice421Page extends Component
{
    private const DICE_COUNT = 3;
    private const MAX_HISTORY = 100;

    private DiceGameStrategy $strategy;

    /**
     * Résolution manuelle de la stratégie pour contourner les limitations
     * d'injection contextuelle de Livewire sur le Container Laravel.
     */
    public function boot(): void
    {
        $this->strategy = app(FourTwoOneStrategy::class);
    }

    /**
     * Dés actuels. Verrouillé pour empêcher l'altération du tirage côté client.
     */
    #[Locked]
    public array $dice = [1, 1, 1];

    /** @var array<bool> Dés conservés par le joueur pour la relance suivante. */
    public array $kept = [false, false, false];

    /**
     * Nombre de lancers effectués. Verrouillé pour garantir le respect de la limite.
     */
    #[Locked]
    public int $throwCount = 0;

    public bool $isOver = false;
    public bool $isWon = false;
    public ?string $combinationLabel = null;
    public ?string $error = null;

    /** @var array<int, array{dice: array<int>, throws: int, won: bool, combination: ?string}> */
    public array $history = [];

    public function mount(): void
    {
        $this->resetGame();
    }

    /**
     * Exécute un lancer de dés si la partie est active et notifie le front.
     */
    public function roll(): void
    {
        $this->error = null;

        if ($this->isOver) {
            $this->error = 'La partie est déjà terminée.';

            // Notifie Alpine.js pour débloquer l'UI même en cas de tentative invalide
            $this->dispatchRollEvent();
            return;
        }

        $action = new RollDiceAction($this->strategy);
        $result = $action->execute($this->dice, $this->kept, $this->throwCount);

        $this->dice = $result->roll->values;
        $this->throwCount = $result->throwCount;
        $this->isWon = $result->isWon;
        $this->isOver = $result->isOver;
        $this->combinationLabel = $result->combination->label();

        if ($this->isOver) {
            $this->recordHistory();
        }

        $this->dispatchRollEvent();
    }

    /**
     * Bascule l'état de conservation d'un dé par son index.
     */
    public function toggleKeep(int $index): void
    {
        if ($this->isOver || ! array_key_exists($index, $this->kept)) {
            return;
        }

        $this->kept[$index] = ! $this->kept[$index];
    }

    /**
     * Réinitialise l'état complet du jeu pour une nouvelle partie.
     */
    public function resetGame(): void
    {
        $this->dice = array_fill(0, self::DICE_COUNT, 1);
        $this->kept = array_fill(0, self::DICE_COUNT, false);
        $this->throwCount = 0;
        $this->isOver = false;
        $this->isWon = false;
        $this->combinationLabel = null;
        $this->error = null;

        $this->dispatch('dice-reset');
    }

    /**
     * Enregistre le résultat de la partie dans l'historique local.
     */
    private function recordHistory(): void
    {
        $this->history[] = [
            'dice' => $this->dice,
            'throws' => $this->throwCount,
            'won' => $this->isWon,
            'combination' => $this->combinationLabel,
        ];

        if (count($this->history) > self::MAX_HISTORY) {
            $this->history = array_slice($this->history, -self::MAX_HISTORY);
        }
    }

    /**
     * Émet l'événement Livewire pour synchroniser l'état avec Alpine.js.
     */
    private function dispatchRollEvent(): void
    {
        $this->dispatch(
            'dice-rolled',
            dice: $this->dice,
            throwCount: $this->throwCount,
            isOver: $this->isOver,
            isWon: $this->isWon,
            combinationLabel: $this->combinationLabel,
        );
    }

    public function maxThrows(): int
    {
        return $this->strategy->maxThrows();
    }

    public function winCount(): int
    {
        return count(array_filter($this->history, fn(array $entry) => $entry['won']));
    }

    public function render()
    {
        $mode = GameModeType::DICE_421->toDto();

        return view('livewire.dice.dice421-page')
            ->layout('layouts.app', [
                'title' => $mode->metaTitle,
                'metaDescription' => $mode->metaDescription,
            ]);
    }
}
