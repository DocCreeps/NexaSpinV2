<?php

namespace App\Domain\SecretNumber\Entities;

use App\Domain\SecretNumber\Enums\SecretNumberHandoffPurpose;
use App\Domain\SecretNumber\Enums\SecretNumberMode;
use App\Domain\SecretNumber\Enums\SecretNumberPhase;
use App\Domain\SecretNumber\Exceptions\InvalidSecretNumberGameException;
use App\Domain\SecretNumber\ValueObjects\SecretNumberPlayers;
use App\Domain\SecretNumber\ValueObjects\SecretNumberRange;

/** Règles et état métier d'une partie de nombre secret. */
final class SecretNumberGame
{
    /** Version du format de toArray() : à incrémenter quand la structure change. */
    public const STATE_VERSION = 1;

    public const MAX_ATTEMPTS_LIMIT = 1000;

    /** Au-delà, publicState() ne renvoie que les dernières propositions pour borner la charge Livewire. */
    public const MAX_PUBLIC_GUESSES = 100;

    private const REQUIRED_STATE_KEYS = [
        'mode', 'minimum', 'maximum', 'max_attempts', 'player_count', 'player_names', 'secret_setter', 'secret',
        'phase', 'attempts', 'solved', 'completed', 'guesses', 'versus_results', 'current_player',
        'handoff_purpose', 'winner', 'feedback', 'revealed_number',
    ];

    /** @var array<int, int> */
    private array $attempts = [];

    /** @var array<int, bool> */
    private array $solved = [];

    /** @var array<int, bool> */
    private array $completed = [];

    /** @var array<int, array<int, array{number: int, hint: string}>> */
    private array $guesses = [];

    /** @var array<int, array{player: int, name: string, setter: int, attempts: int, found: bool, secret: int, guesses: array<int, array{number: int, hint: string}>}> */
    private array $versusResults = [];

    private SecretNumberPhase $phase;

    private int $currentPlayer = 1;

    private int $secretSetter = 1;

    private ?int $secret;

    private SecretNumberHandoffPurpose $handoffPurpose = SecretNumberHandoffPurpose::GUESS;

    private ?int $winner = null;

    private ?string $feedback = null;

    private ?int $revealedNumber = null;

    private function __construct(
        private readonly SecretNumberMode $mode,
        private readonly SecretNumberRange $range,
        private readonly int $maxAttempts,
        private readonly SecretNumberPlayers $players,
        ?int $secret,
        SecretNumberPhase $phase,
        int $secretSetter = 1,
    ) {
        if ($maxAttempts < 0 || $maxAttempts > self::MAX_ATTEMPTS_LIMIT) {
            throw new InvalidSecretNumberGameException('Le nombre maximal d’essais doit être compris entre 0 et 1 000.');
        }

        $this->phase = $phase;
        $this->secretSetter = $secretSetter;
        $this->secret = $secret;

        for ($player = 1; $player <= $players->count; $player++) {
            $this->attempts[$player] = 0;
            $this->solved[$player] = false;
            $this->completed[$player] = false;
            $this->guesses[$player] = [];
        }
    }

    public static function forAi(SecretNumberMode $mode, SecretNumberRange $range, int $maxAttempts, int $secret, SecretNumberPlayers $players): self
    {
        if ($mode === SecretNumberMode::VERSUS || ! $range->contains($secret)) {
            throw new InvalidSecretNumberGameException('Configuration de partie invalide.');
        }

        return new self($mode, $range, $maxAttempts, $players, $secret, SecretNumberPhase::PLAYING);
    }

    public static function forVersus(SecretNumberRange $range, int $maxAttempts, SecretNumberPlayers $players): self
    {
        if ($players->count !== 2) {
            throw new InvalidSecretNumberGameException('Le mode Versus se joue à deux joueurs.');
        }

        return new self(SecretNumberMode::VERSUS, $range, $maxAttempts, $players, null, SecretNumberPhase::CHOOSE_SECRET);
    }

    public function mode(): SecretNumberMode
    {
        return $this->mode;
    }

    public function phase(): SecretNumberPhase
    {
        return $this->phase;
    }

    public function chooseSecret(int $secret): void
    {
        if ($this->mode !== SecretNumberMode::VERSUS || $this->phase !== SecretNumberPhase::CHOOSE_SECRET || ! $this->range->contains($secret)) {
            throw new InvalidSecretNumberGameException('Le nombre choisi est hors de la plage autorisée.');
        }

        $this->secret = $secret;
        $this->currentPlayer = 3 - $this->secretSetter;
        $this->handoffPurpose = SecretNumberHandoffPurpose::GUESS;
        $this->phase = SecretNumberPhase::HANDOFF;
    }

    public function continueAfterHandoff(): void
    {
        if ($this->phase !== SecretNumberPhase::HANDOFF) {
            return;
        }

        $this->phase = $this->mode === SecretNumberMode::VERSUS && $this->handoffPurpose === SecretNumberHandoffPurpose::CHOOSE_SECRET
            ? SecretNumberPhase::CHOOSE_SECRET
            : SecretNumberPhase::PLAYING;
    }

    public function guess(int $guess): void
    {
        if ($this->phase !== SecretNumberPhase::PLAYING || $this->secret === null) {
            throw new InvalidSecretNumberGameException('La partie n’accepte pas de proposition actuellement.');
        }

        if (! $this->range->contains($guess)) {
            throw new InvalidSecretNumberGameException('La proposition est hors de la plage autorisée.');
        }

        $player = $this->currentPlayer;
        foreach ($this->guesses[$player] as $previousGuess) {
            if ($previousGuess['number'] === $guess) {
                throw new InvalidSecretNumberGameException('Vous avez déjà proposé ce nombre. Choisissez-en un autre.');
            }
        }

        $this->attempts[$player]++;
        $hint = $guess === $this->secret ? 'trouvé' : ($guess < $this->secret ? 'plus' : 'moins');
        $this->feedback = $guess === $this->secret ? "{$guess} : trouvé !" : "{$guess} : {$hint}";
        $this->guesses[$player][] = ['number' => $guess, 'hint' => $hint];

        if ($guess === $this->secret) {
            $this->solved[$player] = true;
            $this->completed[$player] = true;
        } elseif ($this->maxAttempts > 0 && $this->attempts[$player] >= $this->maxAttempts) {
            $this->completed[$player] = true;
            $this->feedback .= ' — nombre maximal d’essais atteint.';
        }

        if (! $this->completed[$player]) {
            return;
        }

        match ($this->mode) {
            SecretNumberMode::SOLO => $this->completeSolo($player),
            SecretNumberMode::DUEL => $this->completeDuelTurn($player),
            SecretNumberMode::VERSUS => $this->completeVersusLeg($player),
        };
    }

    private function completeSolo(int $player): void
    {
        $this->winner = $this->solved[$player] ? $player : null;
        $this->finish();
    }

    private function completeDuelTurn(int $player): void
    {
        $this->guesses[$player] = [];

        if ($player < $this->players->count) {
            $this->currentPlayer++;
            $this->handoffPurpose = SecretNumberHandoffPurpose::GUESS;
            $this->feedback = null;
            $this->phase = SecretNumberPhase::HANDOFF;

            return;
        }

        $rankings = $this->rankings();
        $leader = $rankings[0] ?? null;
        $hasUniqueLeader = $leader !== null && (($rankings[1]['rank'] ?? null) !== $leader['rank']);
        $this->winner = $hasUniqueLeader && $leader['found'] ? $leader['player'] : null;
        $this->finish();
    }

    private function completeVersusLeg(int $player): void
    {
        $this->versusResults[] = [
            'player' => $player,
            'name' => $this->players->name($player),
            'setter' => $this->secretSetter,
            'attempts' => $this->attempts[$player],
            'found' => $this->solved[$player],
            'secret' => $this->secret,
            'guesses' => $this->guesses[$player],
        ];

        if ($this->secretSetter === 1) {
            $this->secret = null;
            $this->secretSetter = 2;
            $this->currentPlayer = 2;
            $this->handoffPurpose = SecretNumberHandoffPurpose::CHOOSE_SECRET;
            $this->feedback = null;
            $this->phase = SecretNumberPhase::HANDOFF;

            return;
        }

        $this->resolveVersusWinner();
        $this->finish();
    }

    private function resolveVersusWinner(): void
    {
        $first = $this->versusResults[0] ?? null;
        $second = $this->versusResults[1] ?? null;

        if ($first === null || $second === null) {
            $this->winner = null;

            return;
        }

        if ($first['found'] !== $second['found']) {
            $this->winner = $first['found'] ? $first['player'] : $second['player'];

            return;
        }

        if (! $first['found']) {
            $this->winner = null;

            return;
        }

        $this->winner = $first['attempts'] === $second['attempts']
            ? null
            : ($first['attempts'] < $second['attempts'] ? $first['player'] : $second['player']);
    }

    /** @return array<int, array{rank: int, player: int, name: string, attempts: int, found: bool}> */
    private function rankings(): array
    {
        $players = [];
        for ($player = 1; $player <= $this->players->count; $player++) {
            $players[] = $this->rankingRow($player);
        }

        return $this->rankPlayers($players);
    }

    /** @return array<int, array{rank: int, player: int, name: string, attempts: int, found: bool}> */
    private function progressRankings(): array
    {
        $players = [];
        for ($player = 1; $player <= $this->players->count; $player++) {
            if (! $this->completed[$player] && ! ($player === $this->currentPlayer && $this->attempts[$player] > 0)) {
                continue;
            }

            $players[] = $this->rankingRow($player);
        }

        return $this->rankPlayers($players);
    }

    /** @return array{player: int, name: string, attempts: int, found: bool} */
    private function rankingRow(int $player): array
    {
        return [
            'player' => $player,
            'name' => $this->players->name($player),
            'attempts' => $this->attempts[$player],
            'found' => $this->solved[$player],
        ];
    }

    /**
     * Un joueur qui a trouvé passe devant un joueur qui n'a pas trouvé, puis le moins d'essais l'emporte.
     * Deux joueurs sont ex æquo seulement s'ils ont le même résultat (trouvé ou non) avec autant d'essais.
     *
     * @param  array<int, array{player: int, name: string, attempts: int, found: bool}>  $players
     * @return array<int, array{rank: int, player: int, name: string, attempts: int, found: bool}>
     */
    private function rankPlayers(array $players): array
    {
        usort($players, fn (array $a, array $b) => ($b['found'] <=> $a['found'])
            ?: ($a['attempts'] <=> $b['attempts'])
            ?: ($a['player'] <=> $b['player']));

        $rank = 0;
        $previousKey = null;
        foreach ($players as $index => &$player) {
            $key = ($player['found'] ? '1' : '0').':'.$player['attempts'];
            if ($key !== $previousKey) {
                $rank = $index + 1;
                $previousKey = $key;
            }
            $player['rank'] = $rank;
        }
        unset($player);

        return $players;
    }

    private function finish(): void
    {
        $this->phase = SecretNumberPhase::FINISHED;
        $this->revealedNumber = $this->secret;
        $this->secret = null;
    }

    /** État complet réservé au cache serveur (contient le nombre secret). */
    public function toArray(): array
    {
        return [
            'version' => self::STATE_VERSION,
            'mode' => $this->mode->value,
            'minimum' => $this->range->minimum,
            'maximum' => $this->range->maximum,
            'max_attempts' => $this->maxAttempts,
            'player_count' => $this->players->count,
            'player_names' => $this->players->names,
            'secret_setter' => $this->secretSetter,
            'secret' => $this->secret,
            'phase' => $this->phase->value,
            'attempts' => $this->attempts,
            'solved' => $this->solved,
            'completed' => $this->completed,
            'guesses' => $this->guesses,
            'versus_results' => $this->versusResults,
            'current_player' => $this->currentPlayer,
            'handoff_purpose' => $this->handoffPurpose->value,
            'winner' => $this->winner,
            'feedback' => $this->feedback,
            'revealed_number' => $this->revealedNumber,
            'rankings' => $this->mode === SecretNumberMode::DUEL ? $this->rankings() : [],
            'progress_rankings' => $this->mode === SecretNumberMode::DUEL ? $this->progressRankings() : [],
            'any_player_found' => $this->phase === SecretNumberPhase::FINISHED && in_array(true, $this->solved, true),
        ];
    }

    /**
     * Présentation destinée au navigateur : jamais le nombre secret.
     * En Duel, le joueur suivant ne voit des tours précédents que le classement (nombre d'essais), jamais leurs propositions.
     */
    public function publicState(): array
    {
        $state = $this->toArray();
        unset($state['secret']);

        if ($this->phase !== SecretNumberPhase::FINISHED) {
            unset($state['any_player_found']);
        }

        if ($this->mode === SecretNumberMode::DUEL) {
            $isFinished = $this->phase === SecretNumberPhase::FINISHED;
            $rankingState = $isFinished ? $state['rankings'] : $state['progress_rankings'];
            $state['rankings'] = array_map(
                static fn (array $ranking) => array_filter([
                    'rank' => $ranking['rank'],
                    'player' => $ranking['player'],
                    'name' => $ranking['name'],
                    'attempts' => $ranking['attempts'],
                    'found' => $isFinished ? $ranking['found'] : null,
                ], static fn ($value) => $value !== null),
                $rankingState,
            );
            unset($state['progress_rankings']);

            if ($isFinished) {
                unset($state['solved'], $state['completed'], $state['guesses']);
            } else {
                $player = $this->currentPlayer;
                $state['attempts'] = [$player => $this->attempts[$player]];
                $state['solved'] = [$player => $this->solved[$player]];
                $state['completed'] = [$player => $this->completed[$player]];
                $state['guesses'] = [$player => $this->guesses[$player]];
                $state['feedback'] = $this->phase === SecretNumberPhase::PLAYING ? $this->feedback : null;
            }
        } else {
            unset($state['rankings'], $state['progress_rankings']);
        }

        if ($this->mode === SecretNumberMode::VERSUS) {
            unset($state['solved'], $state['completed']);
            if ($this->phase === SecretNumberPhase::FINISHED) {
                unset($state['guesses']);
            } else {
                unset($state['versus_results']);
                if ($this->phase === SecretNumberPhase::HANDOFF && $this->handoffPurpose === SecretNumberHandoffPurpose::CHOOSE_SECRET) {
                    $state['attempts'] = [];
                    $state['guesses'] = [];
                } else {
                    $player = $this->currentPlayer;
                    $state['attempts'] = [$player => $this->attempts[$player]];
                    $state['guesses'] = [$player => $this->guesses[$player]];
                }
            }
        }

        if (isset($state['guesses'])) {
            $state['guesses'] = array_map($this->limitPublicGuesses(...), $state['guesses']);
        }

        if (isset($state['versus_results'])) {
            $state['versus_results'] = array_map(function (array $result): array {
                $result['guesses'] = $this->limitPublicGuesses($result['guesses']);

                return $result;
            }, $state['versus_results']);
        }

        return $state;
    }

    /**
     * @param  array<int, array{number: int, hint: string}>  $guesses
     * @return array<int, array{number: int, hint: string}>
     */
    private function limitPublicGuesses(array $guesses): array
    {
        return count($guesses) > self::MAX_PUBLIC_GUESSES
            ? array_slice($guesses, -self::MAX_PUBLIC_GUESSES, null, true)
            : $guesses;
    }

    /**
     * Reconstruit une partie depuis le cache serveur.
     *
     * @param  array<string, mixed>  $state
     *
     * @throws InvalidSecretNumberGameException si l'état est d'un format inconnu ou incomplet
     */
    public static function fromArray(array $state): self
    {
        if (($state['version'] ?? null) !== self::STATE_VERSION) {
            throw new InvalidSecretNumberGameException('Format de partie incompatible.');
        }

        foreach (self::REQUIRED_STATE_KEYS as $key) {
            if (! array_key_exists($key, $state)) {
                throw new InvalidSecretNumberGameException('Partie incomplète.');
            }
        }

        $mode = SecretNumberMode::tryFrom((string) $state['mode']);
        $phase = SecretNumberPhase::tryFrom((string) $state['phase']);
        $handoffPurpose = SecretNumberHandoffPurpose::tryFrom((string) $state['handoff_purpose']);
        if ($mode === null || $phase === null || $handoffPurpose === null) {
            throw new InvalidSecretNumberGameException('Partie incomplète.');
        }

        $game = new self(
            $mode,
            new SecretNumberRange($state['minimum'], $state['maximum']),
            $state['max_attempts'],
            SecretNumberPlayers::fromInput($state['player_count'], $state['player_names']),
            $state['secret'],
            $phase,
            $state['secret_setter'],
        );
        $game->attempts = $state['attempts'];
        $game->solved = $state['solved'];
        $game->completed = $state['completed'];
        $game->guesses = $state['guesses'];
        $game->versusResults = $state['versus_results'];
        $game->currentPlayer = $state['current_player'];
        $game->handoffPurpose = $handoffPurpose;
        $game->winner = $state['winner'];
        $game->feedback = $state['feedback'];
        $game->revealedNumber = $state['revealed_number'];

        return $game;
    }
}
