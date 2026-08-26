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

    /**
     * Mode de saisie des résultats : avec scores (par défaut, un champ de
     * score sous chaque nom) ou "sans score" (un bouton Victoire sous
     * chaque nom + un bouton Nul au milieu). Modifiable uniquement avant le
     * lancement de la phase (cf. garde dans start()/addParticipant()).
     */
    public bool $withScores = true;

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
        $this->withScores = $saved['withScores'] ?? true;

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
            'withScores' => $this->withScores,
        ]);
    }

    public function toggleScoreMode(): void
    {
        if ($this->started) {
            return;
        }

        $this->withScores = ! $this->withScores;

        $this->saveProgress();
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

        // Répartition en poules = simple découpage séquentiel de la liste (voir
        // PoolStage::distribute()) : on mélange donc l'ordre des participants ici,
        // une seule fois, avant de créer les poules. L'ordre mélangé est ensuite
        // celui persisté (saveProgress() ci-dessous) et rejoué à l'identique à
        // chaque reconstruction (stage()/RebuildPoolStageAction), pour que la
        // composition des poules reste stable au fil des rechargements.
        shuffle($this->participants);

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

    public function recordResult(string $poolName, int $matchIndex, ?string $manualWinner = null, bool $manualDraw = false): void
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
        $isDraw = $manualDraw;

        if (! $isDraw && $manualWinner === null) {
            if (! is_numeric($valA) || ! is_numeric($valB)) {
                $this->error = 'Veuillez saisir les scores des deux participants (ou déclarer un match nul).';
                return;
            }

            $scoreA = (int) $valA;
            $scoreB = (int) $valB;

            if ($scoreA === $scoreB) {
                // Scores identiques : match nul, pas d'erreur.
                $isDraw = true;
            } else {
                $winnerName = $scoreA > $scoreB
                    ? $match->participantA()->name
                    : $match->participantB()->name;
            }
        }

        try {
            $stage = app(RecordPoolMatchResultAction::class)->execute(
                $this->participants,
                $this->results,
                $poolName,
                $matchIndex,
                $isDraw ? null : $winnerName,
            );
        } catch (InvalidPoolMatchResultException $e) {
            $this->error = $e->getMessage();
            return;
        }

        $this->results[] = [
            'pool' => $poolName,
            'matchIndex' => $matchIndex,
            'winner' => $isDraw ? null : $winnerName,
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
                        fn ($row) => [
                            'name' => $row['participant']->name,
                            'wins' => $row['wins'],
                            'draws' => $row['draws'],
                            'losses' => $row['losses'],
                            'points' => $row['points'],
                        ]
                    )->all()]
                )->all(),
            ]);
        }

        $this->saveProgress();
    }

    /**
     * Valide automatiquement un match dès que les deux scores sont saisis
     * (déclenché par wire:change sur les champs de score). Ne fait rien tant
     * que l'un des deux manque, silencieusement — aucun message d'erreur ne
     * doit apparaître avant que l'utilisateur ait fini de saisir.
     */
    public function autoRecordIfReady(string $poolName, int $matchIndex): void
    {
        $keyA = "{$poolName}_{$matchIndex}_a";
        $keyB = "{$poolName}_{$matchIndex}_b";

        if (is_numeric($this->scores[$keyA] ?? null) && is_numeric($this->scores[$keyB] ?? null)) {
            $this->recordResult($poolName, $matchIndex);
        }
    }

    public function restart(): void
    {
        $this->participants = [];
        $this->participant = '';
        $this->results = [];
        $this->scores = [];
        $this->isComplete = false;
        $this->started = false;
        $this->withScores = true;
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
