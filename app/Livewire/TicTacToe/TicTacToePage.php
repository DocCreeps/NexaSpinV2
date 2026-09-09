<?php

namespace App\Livewire\TicTacToe;

use App\Application\History\HistoryStore;
use App\Application\Home\Enums\GameModeType;
use App\Application\TicTacToe\Actions\ChooseAiMoveAction;
use App\Application\TicTacToe\Actions\ReplayMovesAction;
use App\Domain\TicTacToe\Entities\Board;
use App\Domain\TicTacToe\Enums\DifficultyLevel;
use App\Domain\TicTacToe\Enums\Mark;
use App\Domain\TicTacToe\Enums\TicTacToeOpponentType;
use App\Domain\TicTacToe\Exceptions\InvalidMoveException;
use App\Domain\TicTacToe\Strategies\HeuristicTicTacToeStrategy;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Composant Livewire gérant une partie de morpion (Tic-Tac-Toe).
 */
class TicTacToePage extends Component
{
    private const MAX_HISTORY = 100;

    /** Identifiant de la session en cours */
    #[Locked]
    public string $sessionId;

    /** Mode IA sélectionné par défaut */
    public string $opponentType = TicTacToeOpponentType::AI->value;

    /** Difficulté par défaut */
    public string $difficulty = DifficultyLevel::MEDIUM->value;

    /** @var array<int, int> */
    #[Locked]
    public array $moves = [];

    #[Locked]
    public bool $recorded = false;

    public ?string $error = null;

    /** @var array<int, array<string, mixed>> Parties de la session en cours. */
    public array $history = [];

    public function mount(): void
    {
        $this->sessionId = (string) Str::uuid();
    }

    public function setOpponentType(string $type): void
    {
        // Interdit le changement uniquement si une partie est en cours
        if ($this->isGameInProgress()) {
            return;
        }

        $opponent = TicTacToeOpponentType::tryFrom($type);

        if ($opponent !== null) {
            $this->opponentType = $opponent->value;
        }
    }

    public function setDifficulty(string $difficulty): void
    {
        // Interdit le changement uniquement si une partie est en cours
        if ($this->isGameInProgress()) {
            return;
        }

        $level = DifficultyLevel::tryFrom($difficulty);

        if ($level !== null) {
            $this->difficulty = $level->value;
        }
    }

    private function isGameInProgress(): bool
    {
        return count($this->moves) > 0 && ! $this->board()->isOver();
    }

    public function play(int $position): void
    {
        $this->error = null;

        try {
            $this->board()->play($position);
        } catch (InvalidMoveException $e) {
            $this->error = $e->getMessage();

            return;
        }

        $this->moves[] = $position;
        unset($this->board);

        $this->maybePlayAiMove();

        $this->recordIfFinished();
    }

    private function maybePlayAiMove(): void
    {
        if ($this->opponentType !== TicTacToeOpponentType::AI->value) {
            return;
        }

        if ($this->board()->isOver() || $this->board()->currentTurn() !== Mark::O) {
            return;
        }

        $level = DifficultyLevel::from($this->difficulty);
        $strategy = new HeuristicTicTacToeStrategy($level->errorRate());
        $aiAction = new ChooseAiMoveAction($strategy);

        $aiMove = $aiAction->execute($this->board());

        $this->moves[] = $aiMove;
        unset($this->board);
    }

    public function restart(): void
    {
        $this->moves = [];
        $this->recorded = false;
        $this->error = null;

        unset($this->board);
    }

    public function clearHistory(): void
    {
        $this->history = [];
        $this->sessionId = (string) Str::uuid();

        app(HistoryStore::class)->clear(GameModeType::TIC_TAC_TOE);
    }

    private function recordIfFinished(): void
    {
        $board = $this->board();

        if (! $board->isOver() || $this->recorded) {
            return;
        }

        $this->recorded = true;

        $entry = [
            'winner' => $board->winner()?->value,
            'moves_count' => count($this->moves),
            'opponent_type' => $this->opponentType,
            'difficulty' => $this->opponentType === TicTacToeOpponentType::AI->value ? $this->difficulty : null,
        ];

        $this->history[] = $entry;

        if (count($this->history) > self::MAX_HISTORY) {
            $this->history = array_slice($this->history, -self::MAX_HISTORY);
        }

        $this->persistSession();
    }

    private function persistSession(): void
    {
        $opponent = TicTacToeOpponentType::from($this->opponentType);

        app(HistoryStore::class)->pushSession(GameModeType::TIC_TAC_TOE, $this->sessionId, [
            'opponent' => $opponent->value,
            'opponent_type' => $opponent->value,
            'difficulty' => $this->opponentType === TicTacToeOpponentType::AI->value ? $this->difficulty : null,
            'x_label' => $opponent->xLabel(),
            'o_label' => $opponent->oLabel(),
            'score' => [
                'x' => $this->countWinner('x'),
                'o' => $this->countWinner('o'),
                'draw' => $this->countWinner(null),
            ],
            'games' => $this->history,
            'games_count' => count($this->history),
        ]);
    }

    private function countWinner(?string $winner): int
    {
        return count(array_filter($this->history, fn(array $g) => $g['winner'] === $winner));
    }

    public function opponent(): TicTacToeOpponentType
    {
        return TicTacToeOpponentType::from($this->opponentType);
    }

    #[Computed]
    public function board(): Board
    {
        return app(ReplayMovesAction::class)->execute($this->moves);
    }

    public function render()
    {
        $mode = GameModeType::TIC_TAC_TOE->toDto();

        return view('livewire.tic-tac-toe.tic-tac-toe-page')
            ->layout('layouts.app', [
                'title' => $mode->metaTitle,
                'metaDescription' => $mode->metaDescription,
            ]);
    }
}
