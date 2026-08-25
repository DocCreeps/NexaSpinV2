<?php

namespace App\Livewire\Tournament\Pool;

use App\Application\History\HistoryStore;
use App\Application\Home\Enums\GameModeType;
use App\Application\Tournament\Pool\Actions\CreatePoolStageAction;
use App\Application\Tournament\Pool\Actions\RebuildPoolStageAction;
use App\Application\Tournament\Pool\Actions\RecordPoolMatchResultAction;
use App\Application\Tournament\DTOs\ParticipantListData;
use App\Application\Tournament\TournamentProgressStore;
use App\Domain\Tournament\Pool\Entities\PoolStage;
use App\Domain\Tournament\Pool\Exceptions\InvalidPoolMatchResultException;
use App\Domain\Tournament\Pool\Exceptions\InvalidPoolStageException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

class PoolStagePage extends Component
{
    private const MIN_PARTICIPANTS = 4;
    private const MAX_PARTICIPANTS = 48;
    private const MAX_PARTICIPANT_NAME_LENGTH = 50;

    private const MODE = GameModeType::POOL;

    /** @var array<int, string> */
    public array $participants = [];

    public string $participant = '';

    public ?string $error = null;

    #[Locked]
    public bool $started = false;

    /** @var array<string, int|string|null> */
    public array $scores = [];

    /** @var array<int, array{pool: string, matchIndex: int, winner: string, score_a?: int|null, score_b?: int|null}> */
    #[Locked]
    public array $results = [];

    #[Locked]
    public bool $isComplete = false;

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
            $this->isComplete = $this->stage()->isComplete();
        }
    }

    private function saveProgress(): void
    {
        if ($this->isComplete) {
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
            $this->error = sprintf('Ajoutez au moins %d participants avant de générer les poules.', self::MIN_PARTICIPANTS);
            return;
        }

        try {
            app(CreatePoolStageAction::class)->execute(new ParticipantListData($this->participants));
        } catch (InvalidPoolStageException $e) {
            $this->error = $e->getMessage();
            return;
        }

        $this->results = [];
        $this->scores = [];
        $this->isComplete = false;
        $this->started = true;

        unset($this->stage);

        $this->saveProgress();
    }

    public function recordResult(string $poolName, int $matchIndex, ?string $manualWinner = null): void
    {
        if (! $this->started) {
            return;
        }

        $this->error = null;

        $pool = $this->stage()->poolByName($poolName);
        $match = null;

        if ($pool !== null) {
            foreach ($pool->matches() as $candidate) {
                if ($candidate->index === $matchIndex) {
                    $match = $candidate;
                    break;
                }
            }
        }

        if (! $match || $match->isResolved()) {
            return;
        }

        $keyA = "{$poolName}_{$matchIndex}_a";
        $keyB = "{$poolName}_{$matchIndex}_b";

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
                ? $match->participantA()->name
                : $match->participantB()->name;
        }

        try {
            $stage = app(RecordPoolMatchResultAction::class)->execute(
                $this->participants,
                $this->results,
                $poolName,
                $matchIndex,
                $winnerName,
            );
        } catch (InvalidPoolMatchResultException $e) {
            $this->error = $e->getMessage();
            return;
        }

        $this->results[] = [
            'pool' => $poolName,
            'matchIndex' => $matchIndex,
            'winner' => $winnerName,
            'score_a' => is_numeric($valA) ? (int) $valA : null,
            'score_b' => is_numeric($valB) ? (int) $valB : null,
        ];

        unset($this->stage);

        if ($stage->isComplete()) {
            $this->isComplete = true;

            app(HistoryStore::class)->push(self::MODE, [
                'participants' => $this->participants,
                'standings' => collect($stage->pools())->mapWithKeys(
                    fn ($pool) => [$pool->name => collect($pool->standings())->map(
                        fn ($row) => ['name' => $row['participant']->name, 'wins' => $row['wins']]
                    )->all()]
                )->all(),
            ]);
        }

        $this->saveProgress();
    }

    public function restart(): void
    {
        $this->participants = [];
        $this->participant = '';
        $this->results = [];
        $this->scores = [];
        $this->isComplete = false;
        $this->started = false;
        $this->error = null;

        unset($this->stage);

        app(TournamentProgressStore::class)->clear(self::MODE);
    }

    public function canStart(): bool
    {
        return count($this->participants) >= self::MIN_PARTICIPANTS;
    }

    #[Computed]
    public function stage(): PoolStage
    {
        return app(RebuildPoolStageAction::class)->execute($this->participants, $this->results);
    }

    public function render()
    {
        $mode = self::MODE->toDto();

        return view('livewire.tournament.pool.pool-stage-page')
            ->layout('layouts.app', [
                'title' => $mode->metaTitle,
                'metaDescription' => $mode->metaDescription,
            ]);
    }
}
