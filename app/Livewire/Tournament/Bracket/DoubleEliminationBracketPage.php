<?php

namespace App\Livewire\Tournament\Bracket;

use App\Application\Tournament\Bracket\Actions\CreateDoubleEliminationBracketAction;
use App\Application\Tournament\Bracket\Actions\RebuildDoubleEliminationBracketAction;
use App\Application\Tournament\Bracket\Actions\RecordDoubleEliminationMatchResultAction;
use App\Application\Tournament\DTOs\ParticipantListData;
use App\Application\History\HistoryStore;
use App\Application\Home\Enums\GameModeType;
use App\Application\Tournament\TournamentProgressStore;
use App\Domain\Tournament\Bracket\Entities\DoubleEliminationBracket;
use App\Domain\Tournament\Bracket\Exceptions\InvalidBracketException;
use App\Domain\Tournament\Bracket\Exceptions\InvalidMatchResultException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

class DoubleEliminationBracketPage extends Component
{
    private const MIN_PARTICIPANTS = 4;
    private const MAX_PARTICIPANTS = 32;
    private const MAX_PARTICIPANT_NAME_LENGTH = 50;

    private const MODE = GameModeType::BRACKETJV;

    /** @var array<int, string> */
    public array $participants = [];

    public string $participant = '';

    public ?string $error = null;

    #[Locked]
    public bool $started = false;

    /**
     * Saisie des scores par match. Clés préfixées par section pour éviter toute
     * collision entre l'upper bracket, le lower bracket et la grande finale :
     * "upper_{round}_{position}_a", "lower_{round}_{position}_b",
     * "grand_final_a", "grand_final_reset_b"...
     * @var array<string, int|string|null>
     */
    public array $scores = [];

    /** @var array<int, array{section: string, round: int|null, position: int|null, winner: string, score_a?: int|null, score_b?: int|null}> */
    #[Locked]
    public array $results = [];

    #[Locked]
    public ?string $champion = null;

    public function mount(): void
    {
        $saved = app(TournamentProgressStore::class)->load(self::MODE);

        if ($saved === null) {
            return;
        }

        $this->participants = $saved['participants'] ?? [];
        $this->results = $saved['results'] ?? [];
        $this->started = $saved['started'] ?? false;

        if ($this->started && $this->participants !== []) {
            $bracket = app(RebuildDoubleEliminationBracketAction::class)->execute($this->participants, $this->results);
            $this->champion = $bracket->isComplete() ? $bracket->champion()?->name : null;
        }
    }

    private function saveProgress(): void
    {
        if ($this->champion !== null) {
            app(TournamentProgressStore::class)->clear(self::MODE);

            return;
        }

        app(TournamentProgressStore::class)->save(self::MODE, [
            'participants' => $this->participants,
            'results' => $this->results,
            'started' => $this->started,
        ]);
    }

    public function addParticipant(): void
    {
        if ($this->started) {
            return;
        }

        $this->error = null;
        $name = trim($this->participant);

        if ($name === '') {
            return;
        }

        if (mb_strlen($name) > self::MAX_PARTICIPANT_NAME_LENGTH) {
            $this->error = sprintf('Le nom du participant ne peut pas dépasser %d caractères.', self::MAX_PARTICIPANT_NAME_LENGTH);
            return;
        }

        if (count($this->participants) >= self::MAX_PARTICIPANTS) {
            $this->error = sprintf('Vous ne pouvez pas ajouter plus de %d participants.', self::MAX_PARTICIPANTS);
            return;
        }

        $exists = collect($this->participants)
            ->contains(fn (string $existing) => mb_strtolower($existing) === mb_strtolower($name));

        if ($exists) {
            $this->error = 'Ce participant existe déjà.';
            return;
        }

        $this->participants[] = $name;
        $this->participant = '';

        $this->saveProgress();
    }

    public function removeParticipant(int $index): void
    {
        if ($this->started) {
            return;
        }

        unset($this->participants[$index]);
        $this->participants = array_values($this->participants);

        $this->saveProgress();
    }

    public function start(): void
    {
        $this->error = null;

        if (count($this->participants) < self::MIN_PARTICIPANTS) {
            $this->error = sprintf('Ajoutez au moins %d participants avant de générer le bracket.', self::MIN_PARTICIPANTS);
            return;
        }

        try {
            app(CreateDoubleEliminationBracketAction::class)->execute(new ParticipantListData($this->participants));
        } catch (InvalidBracketException $e) {
            $this->error = $e->getMessage();
            return;
        }

        $this->results = [];
        $this->scores = [];
        $this->champion = null;
        $this->started = true;

        unset($this->bracket);

        $this->saveProgress();
    }

    /**
     * Enregistre le résultat d'un match, quelle que soit sa section
     * (upper / lower / grand_final / grand_final_reset).
     */
    public function recordResult(string $section, ?int $round, ?int $position, ?string $manualWinner = null): void
    {
        if (! $this->started) {
            return;
        }

        $this->error = null;

        $match = $this->findMatch($section, $round, $position);

        if (! $match || ! $match->isPlayable()) {
            return;
        }

        $prefix = $round !== null ? "{$section}_{$round}_{$position}" : $section;
        $keyA = "{$prefix}_a";
        $keyB = "{$prefix}_b";

        $valA = $this->scores[$keyA] ?? null;
        $valB = $this->scores[$keyB] ?? null;

        $winnerName = $manualWinner;

        if ($manualWinner === null) {
            if (! is_numeric($valA) || ! is_numeric($valB)) {
                $this->error = 'Veuillez saisir les scores des deux participants.';
                return;
            }

            $scoreA = (int) $valA;
            $scoreB = (int) $valB;

            if ($scoreA === $scoreB) {
                $this->error = 'Il ne peut pas y avoir d’égalité.';
                return;
            }

            $winnerName = $scoreA > $scoreB
                ? $match->participantA()?->name
                : $match->participantB()?->name;
        }

        if (! $winnerName) {
            $this->error = 'Impossible de déterminer le vainqueur.';
            return;
        }

        try {
            $bracket = app(RecordDoubleEliminationMatchResultAction::class)->execute(
                $this->participants,
                $this->results,
                $section,
                $round,
                $position,
                $winnerName,
            );
        } catch (InvalidMatchResultException $e) {
            $this->error = $e->getMessage();
            return;
        }

        $this->results[] = [
            'section' => $section,
            'round' => $round,
            'position' => $position,
            'winner' => $winnerName,
            'score_a' => is_numeric($valA) ? (int) $valA : null,
            'score_b' => is_numeric($valB) ? (int) $valB : null,
        ];

        unset($this->bracket);

        if ($bracket->isComplete()) {
            $this->champion = $bracket->champion()?->name;

            app(HistoryStore::class)->push(self::MODE, [
                'champion' => $this->champion,
                'participants' => $this->participants,
            ]);
        }

        $this->saveProgress();
    }

    private function findMatch(string $section, ?int $round, ?int $position)
    {
        $bracket = $this->bracket();

        return match ($section) {
            'upper' => $bracket->upperRounds()[$round][$position] ?? null,
            'lower' => $bracket->lowerRounds()[$round][$position] ?? null,
            'grand_final' => $bracket->grandFinal(),
            'grand_final_reset' => $bracket->grandFinalReset(),
            default => null,
        };
    }

    public function restart(): void
    {
        $this->participants = [];
        $this->participant = '';
        $this->results = [];
        $this->scores = [];
        $this->champion = null;
        $this->started = false;
        $this->error = null;

        unset($this->bracket);

        app(TournamentProgressStore::class)->clear(self::MODE);
    }

    public function canStart(): bool
    {
        return count($this->participants) >= self::MIN_PARTICIPANTS;
    }

    #[Computed]
    public function bracket(): DoubleEliminationBracket
    {
        return app(RebuildDoubleEliminationBracketAction::class)->execute($this->participants, $this->results);
    }

    public function render()
    {
        $mode = self::MODE->toDto();

        return view('livewire.tournament.bracket.double-elimination-bracket-page')
            ->layout('layouts.app', [
                'title' => $mode->metaTitle,
                'metaDescription' => $mode->metaDescription,
            ]);
    }
}
