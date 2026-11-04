<?php

namespace App\Livewire\SecretNumber;

use App\Application\History\HistoryStore;
use App\Application\Home\Enums\GameModeType;
use App\Application\SecretNumber\Actions\ChooseSecretNumberAction;
use App\Application\SecretNumber\Actions\ContinueSecretNumberAfterHandoffAction;
use App\Application\SecretNumber\Actions\CreateSecretNumberGameAction;
use App\Application\SecretNumber\Actions\RecordSecretNumberResultAction;
use App\Application\SecretNumber\Actions\SubmitSecretNumberGuessAction;
use App\Application\SecretNumber\SecretNumberGameStore;
use App\Domain\SecretNumber\Entities\SecretNumberGame;
use App\Domain\SecretNumber\Enums\SecretNumberMode;
use App\Domain\SecretNumber\Enums\SecretNumberPhase;
use App\Domain\SecretNumber\Exceptions\InvalidSecretNumberGameException;
use App\Domain\SecretNumber\ValueObjects\SecretNumberPlayers;
use App\Domain\SecretNumber\ValueObjects\SecretNumberRange;
use Illuminate\Support\Str;
use Livewire\Attributes\Locked;
use Livewire\Component;

/** Adaptateur Livewire : valide les entrées et délègue les règles à l'Application et au Domain. */
class SecretNumberPage extends Component
{
    private const HISTORY_LIMIT = 50;

    public string $gameMode = 'solo';

    public int $minNumber = 0;

    public int $maxNumber = 1000;

    /** Zéro signifie sans limite. */
    public int $maxAttempts = 0;

    public int $playerCount = 2;

    /** @var array<int, string> */
    public array $playerNames = [1 => 'Joueur 1', 2 => 'Joueur 2'];

    public string $guess = '';

    public string $secretInput = '';

    public ?string $error = null;

    #[Locked]
    public string $roundId;

    /** État de présentation uniquement : le nombre secret n'y est jamais inclus. */
    #[Locked]
    public array $gameState = [];

    /** @var array<int, array<string, mixed>> */
    #[Locked]
    public array $history = [];

    public function mount(): void
    {
        $this->roundId = (string) Str::uuid();
        $this->refreshHistory();
    }

    public function setGameMode(string $mode): void
    {
        if (! $this->canChangeSettings() || SecretNumberMode::tryFrom($mode) === null) {
            return;
        }

        $this->gameMode = $mode;
        $this->error = null;
    }

    public function setRangePreset(string $preset): void
    {
        $range = SecretNumberRange::fromPreset($preset);
        if (! $this->canChangeSettings() || $range === null) {
            return;
        }

        $this->minNumber = $range->minimum;
        $this->maxNumber = $range->maximum;
        $this->error = null;
    }

    public function startGame(CreateSecretNumberGameAction $action): void
    {
        if (! $this->canChangeSettings()) {
            return;
        }

        $this->error = null;

        $mode = SecretNumberMode::tryFrom($this->gameMode);
        if ($mode === null) {
            $this->error = 'Choisissez un mode de jeu valide.';

            return;
        }

        try {
            $players = SecretNumberPlayers::fromInput($mode === SecretNumberMode::DUEL ? $this->playerCount : 2, $this->playerNames);
            $game = $action->execute($mode, new SecretNumberRange($this->minNumber, $this->maxNumber), $this->maxAttempts, $players);
        } catch (InvalidSecretNumberGameException $exception) {
            $this->error = $exception->getMessage();

            return;
        }

        $this->store()->clear($this->roundId);
        $this->roundId = (string) Str::uuid();
        $this->playerNames = $players->names;
        $this->guess = '';
        $this->secretInput = '';
        $this->saveState($game);
    }

    public function submitSecret(ChooseSecretNumberAction $action): void
    {
        $this->error = null;
        if ($this->phase() !== SecretNumberPhase::CHOOSE_SECRET) {
            return;
        }

        $game = $this->currentGame();
        if ($game === null) {
            $this->error = 'La partie a expiré. Lancez une nouvelle partie.';

            return;
        }

        $secret = filter_var($this->secretInput, FILTER_VALIDATE_INT);
        if ($secret === false) {
            $this->error = 'Entrez un nombre entier.';

            return;
        }

        try {
            $action->execute($game, $secret);
        } catch (InvalidSecretNumberGameException $exception) {
            $this->error = $exception->getMessage();

            return;
        }

        $this->secretInput = '';
        $this->saveState($game);
        $this->dispatch('secret-number-handoff');
    }

    public function continueAfterHandoff(ContinueSecretNumberAfterHandoffAction $action): void
    {
        $this->error = null;
        if ($this->phase() !== SecretNumberPhase::HANDOFF) {
            return;
        }

        $game = $this->currentGame();
        if ($game === null) {
            $this->error = 'La partie a expiré. Lancez une nouvelle partie.';

            return;
        }

        $action->execute($game);
        $this->saveState($game);
    }

    public function submitGuess(SubmitSecretNumberGuessAction $action, RecordSecretNumberResultAction $recordResult): void
    {
        $this->error = null;
        if ($this->phase() !== SecretNumberPhase::PLAYING) {
            return;
        }

        $game = $this->currentGame();
        if ($game === null) {
            $this->error = 'La partie a expiré. Lancez une nouvelle partie.';

            return;
        }

        $guess = filter_var($this->guess, FILTER_VALIDATE_INT);
        if ($guess === false) {
            $this->error = 'Entrez un nombre entier.';

            return;
        }

        try {
            $action->execute($game, $guess);
        } catch (InvalidSecretNumberGameException $exception) {
            $this->error = $exception->getMessage();

            return;
        }

        $this->guess = '';
        $this->saveState($game);

        if ($game->phase() === SecretNumberPhase::FINISHED) {
            $recordResult->execute($game);
            $this->refreshHistory();
            // Le récapitulatif est en historique : la partie n'a plus de raison de rester côté serveur.
            $this->store()->clear($this->roundId);
        }
    }

    public function returnToModeSelection(): void
    {
        $this->store()->clear($this->roundId);
        $this->gameState = [];
        $this->guess = '';
        $this->secretInput = '';
        $this->error = null;
    }

    public function clearHistory(): void
    {
        app(HistoryStore::class)->clear(GameModeType::SECRET_NUMBER);
        $this->history = [];
    }

    private function phase(): SecretNumberPhase
    {
        return SecretNumberPhase::tryFrom((string) ($this->gameState['phase'] ?? '')) ?? SecretNumberPhase::SETUP;
    }

    private function canChangeSettings(): bool
    {
        return in_array($this->phase(), [SecretNumberPhase::SETUP, SecretNumberPhase::FINISHED], true);
    }

    private function store(): SecretNumberGameStore
    {
        return app(SecretNumberGameStore::class);
    }

    private function currentGame(): ?SecretNumberGame
    {
        return $this->store()->load($this->roundId);
    }

    private function saveState(SecretNumberGame $game): void
    {
        $this->gameState = $game->publicState();
        $this->store()->save($this->roundId, $game);
    }

    private function refreshHistory(): void
    {
        $this->history = app(HistoryStore::class)->all(GameModeType::SECRET_NUMBER, self::HISTORY_LIMIT);
    }

    public function render()
    {
        $mode = GameModeType::SECRET_NUMBER->toDto();

        return view('livewire.secret-number.secret-number-page', [
            'modes' => SecretNumberMode::cases(),
            'rangePresets' => SecretNumberRange::presets(),
        ])->layout('layouts.app', [
            'title' => $mode->metaTitle,
            'metaDescription' => $mode->metaDescription,
        ]);
    }
}
