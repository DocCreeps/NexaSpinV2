<?php

namespace App\Livewire\TicTacToe;

use App\Application\History\HistoryStore;
use App\Application\Home\Enums\GameModeType;
use App\Application\TicTacToe\Actions\ChooseAiMoveAction;
use App\Application\TicTacToe\Actions\ReplayMovesAction;
use App\Domain\TicTacToe\Entities\Board;
use App\Domain\TicTacToe\Enums\Mark;
use App\Domain\TicTacToe\Enums\TicTacToeOpponentType;
use App\Domain\TicTacToe\Exceptions\InvalidMoveException;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Composant Livewire gérant une partie de morpion, contre l'IA ou à deux
 * joueurs (même écran). Suit le pattern de PoolStagePage/DoubleEliminationBracketPage :
 * seule la liste des coups est persistée, l'entité Board est reconstruite à
 * chaque accès via #[Computed] + ReplayMovesAction, jamais stockée
 * directement en propriété publique.
 *
 * Regroupement par session : toutes les parties jouées depuis l'ouverture de
 * la page (ou depuis le dernier "Vider l'historique") partagent le même
 * $sessionId et sont fusionnées en une seule entrée dans l'historique global
 * (/historique) via HistoryStore::pushSession(), mise à jour partie après
 * partie plutôt qu'empilée une par une.
 */
class TicTacToePage extends Component
{
    private const MAX_HISTORY = 100;

    /** Identifiant de la session en cours, régénéré à l'ouverture de la page et à chaque "Vider l'historique". */
    #[Locked]
    public string $sessionId;

    /** 'ai' ou 'local'. Verrouillé dès que la session contient au moins une partie (ou une partie en cours). */
    public string $opponentType = TicTacToeOpponentType::LOCAL->value;

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

    /**
     * Change l'adversaire (IA / local). Refusé une fois la session commencée
     * (partie en cours ou déjà terminée) pour ne pas mélanger deux types
     * d'adversaire dans une même session groupée.
     */
    public function setOpponentType(string $type): void
    {
        if ($this->history !== [] || $this->moves !== []) {
            return;
        }

        $opponent = TicTacToeOpponentType::tryFrom($type);

        if ($opponent === null) {
            return;
        }

        $this->opponentType = $opponent->value;
    }

    public function play(int $position, ChooseAiMoveAction $aiAction): void
    {
        $this->error = null;

        try {
            // Valide le coup sur la reconstruction courante du plateau, avant
            // de l'ajouter à la liste persistée. Un coup invalide ne modifie
            // donc jamais $this->moves.
            $this->board()->play($position);
        } catch (InvalidMoveException $e) {
            $this->error = $e->getMessage();

            return;
        }

        $this->moves[] = $position;
        unset($this->board);

        $this->maybePlayAiMove($aiAction);

        $this->recordIfFinished();
    }

    /**
     * En mode IA, joue automatiquement le coup de O juste après celui de X
     * (le joueur humain a toujours X et commence). Ne fait rien en local.
     */
    private function maybePlayAiMove(ChooseAiMoveAction $aiAction): void
    {
        if ($this->opponentType !== TicTacToeOpponentType::AI->value) {
            return;
        }

        if ($this->board()->isOver() || $this->board()->currentTurn() !== Mark::O) {
            return;
        }

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
        return count(array_filter($this->history, fn (array $g) => $g['winner'] === $winner));
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
