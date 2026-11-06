<?php

namespace App\Livewire\Sticks;

use App\Application\History\HistoryStore;
use App\Application\Home\Enums\GameModeType;
use App\Application\Sticks\Actions\ChooseAiMoveAction;
use App\Application\Sticks\Actions\ReplaySticksMovesAction;
use App\Domain\Sticks\Entities\SticksGame;
use App\Domain\Sticks\Enums\SticksDifficulty;
use App\Domain\Sticks\Enums\SticksOpponentType;
use App\Domain\Sticks\Enums\SticksVariant;
use App\Domain\Sticks\Exceptions\InvalidSticksMoveException;
use App\Domain\Sticks\Strategies\OptimalSticksStrategy;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Composant Livewire gérant une partie du jeu des bâtonnets (duel local ou contre l'IA).
 *
 * @property-read SticksGame $game Partie reconstruite en rejouant les coups (propriété calculée)
 */
class SticksPage extends Component
{
    private const MAX_HISTORY = 100;

    /** Délai (ms) avant que l'IA ne joue son coup, pour laisser le temps de suivre la partie. */
    public const AI_DELAY_MS = 1200;

    /** Délai (ms) entre le dernier coup et l'annonce du résultat (et son enregistrement en historique). */
    public const RESULT_DELAY_MS = 1600;

    public const MAX_NAME_LENGTH = 16;

    /** Identifiant de la session en cours */
    #[Locked]
    public string $sessionId;

    #[Locked]
    public string $opponentType = SticksOpponentType::AI->value;

    #[Locked]
    public string $difficulty = SticksDifficulty::MEDIUM->value;

    #[Locked]
    public int $stickCount = SticksGame::DEFAULT_STICKS;

    /** Règle de fin : le dernier bâtonnet fait perdre (classique) ou gagner. */
    #[Locked]
    public string $variant = SticksVariant::LAST_LOSES->value;

    /** Duel local : prénoms des joueurs (vides = « Joueur 1 » / « Joueur 2 »). */
    #[Locked]
    public string $player1Name = '';

    #[Locked]
    public string $player2Name = '';

    /** Duel local : joueur (1 ou 2) qui retire en premier. */
    #[Locked]
    public int $localStarter = 1;

    /** Contre l'IA : l'IA retire en premier (sinon, c'est l'humain). */
    #[Locked]
    public bool $aiStarts = false;

    /** @var array<int, array<int, int>> Bâtonnets retirés à chaque coup. */
    #[Locked]
    public array $moves = [];

    /** La partie est lancée : les réglages sont figés et le plateau est affiché. */
    #[Locked]
    public bool $started = false;

    /** Le message de fin est affiché (après le délai qui suit le dernier coup). */
    #[Locked]
    public bool $resultRevealed = false;

    #[Locked]
    public bool $recorded = false;

    public ?string $error = null;

    /** @var array<int, array<string, mixed>> Parties de la série en cours (mode de versus actuel). */
    #[Locked]
    public array $history = [];

    /**
     * Série (parties + identifiant de session) de l'autre mode de versus, remise de côté
     * quand on change de mode et retrouvée quand on y revient.
     *
     * @var array<string, array{history: array<int, array<string, mixed>>, sessionId: string}>
     */
    #[Locked]
    public array $stash = [];

    public function mount(): void
    {
        $this->sessionId = (string) Str::uuid();
    }

    public function setOpponentType(string $type): void
    {
        if ($this->isConfigLocked()) {
            return;
        }

        $opponent = SticksOpponentType::tryFrom($type);

        if ($opponent === null || $opponent->value === $this->opponentType) {
            return;
        }

        // Chaque mode de versus a sa propre série (score + historique) : on met celle du mode
        // quitté de côté et on retrouve celle du mode choisi (ou on en ouvre une nouvelle).
        $this->stash[$this->opponentType] = ['history' => $this->history, 'sessionId' => $this->sessionId];

        $next = $this->stash[$opponent->value] ?? ['history' => [], 'sessionId' => (string) Str::uuid()];

        $this->opponentType = $opponent->value;
        $this->history = $next['history'];
        $this->sessionId = $next['sessionId'];

        unset($this->stash[$opponent->value]);
    }

    public function setDifficulty(string $difficulty): void
    {
        if ($this->isConfigLocked()) {
            return;
        }

        $level = SticksDifficulty::tryFrom($difficulty);

        if ($level !== null) {
            $this->difficulty = $level->value;
        }
    }

    public function setStickCount(int $count): void
    {
        if ($this->isConfigLocked()) {
            return;
        }

        $this->stickCount = max(SticksGame::MIN_STICKS, min(SticksGame::MAX_STICKS, $count));
    }

    public function setVariant(string $variant): void
    {
        if ($this->isConfigLocked()) {
            return;
        }

        $rule = SticksVariant::tryFrom($variant);

        if ($rule !== null) {
            $this->variant = $rule->value;
        }
    }

    /** Duel local : renseigne les prénoms (16 caractères max, vide = nom par défaut). */
    public function setPlayerNames(string $player1, string $player2): void
    {
        if ($this->isConfigLocked()) {
            return;
        }

        $this->player1Name = mb_substr(trim(strip_tags($player1)), 0, self::MAX_NAME_LENGTH);
        $this->player2Name = mb_substr(trim(strip_tags($player2)), 0, self::MAX_NAME_LENGTH);
    }

    /** Duel local : choisit qui retire en premier. */
    public function setLocalStarter(int $player): void
    {
        if ($this->isConfigLocked() || ($player !== 1 && $player !== 2)) {
            return;
        }

        $this->localStarter = $player;
    }

    public function setAiStarts(bool $aiStarts): void
    {
        if ($this->isConfigLocked()) {
            return;
        }

        $this->aiStarts = $aiStarts;
    }

    /**
     * Les réglages (adversaire, difficulté, nombre de bâtonnets, premier joueur)
     * conditionnent la reconstruction de la partie : on les fige dès le lancement.
     */
    private function isConfigLocked(): bool
    {
        return $this->started || $this->moves !== [];
    }

    /**
     * Lance la partie avec les réglages choisis. Si l'IA commence,
     * son coup est joué par `playAiTurn()` après un court délai.
     */
    public function startGame(): void
    {
        if ($this->started) {
            return;
        }

        $this->started = true;
        $this->error = null;
        $this->recorded = false;
        $this->resultRevealed = false;
    }

    /**
     * Retire les bâtonnets choisis par le joueur dont c'est le tour.
     *
     * @param  array<int, mixed>  $indices
     */
    public function take(array $indices): void
    {
        $this->error = null;

        if (! $this->started) {
            return;
        }

        $game = $this->game;

        if ($game->isOver()) {
            return;
        }

        // Contre l'IA, seul le joueur 1 (l'humain) joue via l'interface.
        if ($this->isVsAi() && $game->currentPlayer() !== 1) {
            return;
        }

        foreach ($indices as $index) {
            if (! is_int($index)) {
                $this->error = 'Sélection invalide.';

                return;
            }
        }

        $indices = array_values($indices);
        sort($indices);

        try {
            $game->remove($indices);
        } catch (InvalidSticksMoveException $e) {
            $this->error = $e->getMessage();

            return;
        }

        $this->moves[] = $indices;
        unset($this->game);
    }

    /**
     * Retire les N premiers bâtonnets encore en jeu (raccourci des boutons « Retirer 1/2/3 »).
     */
    public function takeFirst(int $count): void
    {
        if (! $this->started) {
            return;
        }

        $this->take(array_slice($this->game->remainingSticks(), 0, $count));
    }

    /**
     * Joue le coup de l'IA. Appelé par la vue après `AI_DELAY_MS` ms,
     * uniquement quand c'est bien à l'IA de jouer.
     */
    public function playAiTurn(): void
    {
        if (! $this->aiPending()) {
            return;
        }

        $this->playAiMove();
    }

    /**
     * Annonce le résultat et l'enregistre dans l'historique. Appelé par la vue
     * après `RESULT_DELAY_MS` ms, pour laisser voir le dernier coup d'abord.
     */
    public function revealResult(): void
    {
        if (! $this->started || $this->resultRevealed || ! $this->game->isOver()) {
            return;
        }

        $this->resultRevealed = true;
        $this->recordIfFinished();
    }

    /** La partie est terminée mais le résultat n'est pas encore annoncé. */
    public function resultPending(): bool
    {
        return $this->started && ! $this->resultRevealed && $this->game->isOver();
    }

    /**
     * Score cumulé de la session.
     *
     * @return array{p1: int, p2: int}
     */
    public function score(): array
    {
        return ['p1' => $this->countWinner(1), 'p2' => $this->countWinner(2)];
    }

    /** C'est à l'IA de jouer (partie lancée, contre l'IA, non terminée). */
    public function aiPending(): bool
    {
        if (! $this->started || ! $this->isVsAi()) {
            return false;
        }

        $game = $this->game;

        return ! $game->isOver() && $game->currentPlayer() === 2;
    }

    /**
     * Dernier coup joué : qui, et combien de bâtonnets retirés.
     *
     * @return array{player: int, count: int}|null
     */
    public function lastMove(): ?array
    {
        $total = count($this->moves);

        if ($total === 0) {
            return null;
        }

        $first = $this->firstPlayer();
        $other = $first === 1 ? 2 : 1;

        return [
            'player' => ($total - 1) % 2 === 0 ? $first : $other,
            'count' => count($this->moves[$total - 1]),
        ];
    }

    private function playAiMove(): void
    {
        $level = SticksDifficulty::from($this->difficulty);
        $action = new ChooseAiMoveAction(new OptimalSticksStrategy($level->errorRate()));

        $this->moves[] = $action->execute($this->game);
        unset($this->game);
    }

    /**
     * Duel local : annule le dernier coup joué (partie en cours uniquement).
     */
    public function undo(): void
    {
        if (! $this->started || $this->isVsAi() || $this->moves === [] || $this->game->isOver()) {
            return;
        }

        array_pop($this->moves);
        unset($this->game);

        $this->error = null;
    }

    /**
     * Revanche : rejoue tout de suite avec les mêmes réglages (partie terminée et résultat annoncé).
     * En duel local, les joueurs échangent leur ordre de départ.
     */
    public function rematch(): void
    {
        if (! $this->started || ! $this->resultRevealed || ! $this->game->isOver()) {
            return;
        }

        if (! $this->isVsAi()) {
            $this->localStarter = 3 - $this->localStarter;
        }

        $this->moves = [];
        $this->recorded = false;
        $this->resultRevealed = false;
        $this->error = null;

        unset($this->game);
    }

    /**
     * Retour à l'écran de réglages (autres paramètres, ou abandon de la partie en cours).
     */
    public function restart(): void
    {
        $this->moves = [];
        $this->started = false;
        $this->recorded = false;
        $this->resultRevealed = false;
        $this->error = null;

        unset($this->game);
    }

    public function clearHistory(): void
    {
        $this->history = [];
        $this->stash = [];
        $this->sessionId = (string) Str::uuid();

        app(HistoryStore::class)->clear(GameModeType::STICKS);
    }

    private function recordIfFinished(): void
    {
        $game = $this->game;

        if (! $game->isOver() || $this->recorded) {
            return;
        }

        $this->recorded = true;

        $this->history[] = [
            'winner' => $game->winner(),
            'winner_label' => $this->playerLabel((int) $game->winner()),
            'variant' => $this->variant,
            'variant_label' => SticksVariant::from($this->variant)->label(),
            'moves_count' => count($this->moves),
            'stick_count' => $this->stickCount,
            'first_player' => $game->firstPlayer,
            'opponent_type' => $this->opponentType,
            'difficulty' => $this->isVsAi() ? $this->difficulty : null,
        ];

        if (count($this->history) > self::MAX_HISTORY) {
            $this->history = array_slice($this->history, -self::MAX_HISTORY);
        }

        $this->persistSession();
    }

    private function persistSession(): void
    {
        $opponent = $this->opponent();
        $level = $this->isVsAi() ? SticksDifficulty::from($this->difficulty) : null;

        app(HistoryStore::class)->pushSession(GameModeType::STICKS, $this->sessionId, [
            'opponent' => $opponent->value,
            'opponent_type' => $opponent->value,
            'vs_ai' => $this->isVsAi(),
            'difficulty' => $level?->value,
            'difficulty_label' => $level?->label(),
            'p1_label' => $this->playerLabel(1),
            'p2_label' => $this->playerLabel(2),
            'score' => [
                'p1' => $this->countWinner(1),
                'p2' => $this->countWinner(2),
            ],
            'games' => $this->history,
            'games_count' => count($this->history),
        ]);
    }

    private function countWinner(int $player): int
    {
        return count(array_filter($this->history, fn (array $g) => $g['winner'] === $player));
    }

    public function opponent(): SticksOpponentType
    {
        return SticksOpponentType::from($this->opponentType);
    }

    public function isVsAi(): bool
    {
        return $this->opponentType === SticksOpponentType::AI->value;
    }

    /** Joueur qui retire en premier dans la partie en cours. */
    private function firstPlayer(): int
    {
        if ($this->isVsAi()) {
            return $this->aiStarts ? 2 : 1;
        }

        return $this->localStarter;
    }

    /** Nom affiché d'un joueur (prénom saisi en duel local, sinon libellé par défaut). */
    public function playerLabel(int $player): string
    {
        if (! $this->isVsAi()) {
            $name = $player === 1 ? $this->player1Name : $this->player2Name;

            if ($name !== '') {
                return $name;
            }
        }

        return $player === 1 ? $this->opponent()->p1Label() : $this->opponent()->p2Label();
    }

    public function variantRule(): SticksVariant
    {
        return SticksVariant::from($this->variant);
    }

    #[Computed]
    public function game(): SticksGame
    {
        return app(ReplaySticksMovesAction::class)->execute($this->stickCount, $this->moves, $this->firstPlayer(), $this->variantRule());
    }

    public function render()
    {
        $mode = GameModeType::STICKS->toDto();

        return view('livewire.sticks.sticks-page')
            ->layout('layouts.app', [
                'title' => $mode->metaTitle,
                'metaDescription' => $mode->metaDescription,
            ]);
    }
}
