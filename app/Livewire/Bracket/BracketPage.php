<?php

namespace App\Livewire\Bracket;

use App\Application\Bracket\Actions\CreateBracketAction;
use App\Application\Bracket\Actions\RebuildBracketAction;
use App\Application\Bracket\Actions\RecordMatchResultAction;
use App\Application\Bracket\DTOs\BracketData;
use App\Application\History\HistoryStore;
use App\Application\Home\Enums\GameModeType;
use App\Domain\Bracket\Entities\Bracket;
use App\Domain\Bracket\Exceptions\InvalidBracketException;
use App\Domain\Bracket\Exceptions\InvalidMatchResultException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

class BracketPage extends Component
{
    private const MIN_PARTICIPANTS = 4;
    private const MAX_PARTICIPANTS = 32;
    private const MAX_PARTICIPANT_NAME_LENGTH = 50;

    /** @var array<int, string> */
    public array $participants = [];

    public string $participant = '';

    public ?string $error = null;

    #[Locked]
    public bool $started = false;

    /**
     * Saisie des scores par match.
     * Clés au format : "{round}_{position}_a" et "{round}_{position}_b"
     * @var array<string, int|string|null>
     */
    public array $scores = [];

    /** @var array<int, array{round: int, position: int, winner: string, score_a?: int|null, score_b?: int|null}> */
    #[Locked]
    public array $results = [];

    #[Locked]
    public ?string $champion = null;

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
            ->contains(fn(string $existing) => mb_strtolower($existing) === mb_strtolower($name));

        if ($exists) {
            $this->error = 'Ce participant existe déjà.';
            return;
        }

        $this->participants[] = $name;
        $this->participant = '';
    }

    public function removeParticipant(int $index): void
    {
        if ($this->started) {
            return;
        }

        unset($this->participants[$index]);
        $this->participants = array_values($this->participants);
    }

    public function start(): void
    {
        $this->error = null;

        if (count($this->participants) < self::MIN_PARTICIPANTS) {
            $this->error = sprintf('Ajoutez au moins %d participants avant de générer le bracket.', self::MIN_PARTICIPANTS);
            return;
        }

        try {
            app(CreateBracketAction::class)->execute(new BracketData($this->participants));
        } catch (InvalidBracketException $e) {
            $this->error = $e->getMessage();
            return;
        }

        $this->results = [];
        $this->scores = [];
        $this->champion = null;
        $this->started = true;

        unset($this->bracket);
    }

    /**
     * Enregistre le résultat d'un match à partir des scores ou directement par le nom du vainqueur.
     */
    public function recordResult(int $round, int $position, ?string $manualWinner = null): void
    {
        if (! $this->started) {
            return;
        }

        $this->error = null;

        // Récupération sécurisée du match via l'arbre complet des rounds
        $match = null;
        $rounds = $this->bracket()->rounds();

        if (isset($rounds[$round])) {
            foreach ($rounds[$round] as $m) {
                if ($m->position === $position) {
                    $match = $m;
                    break;
                }
            }
        }

        if (! $match || ! $match->isPlayable()) {
            return;
        }

        $keyA = "{$round}_{$position}_a";
        $keyB = "{$round}_{$position}_b";

        $valA = $this->scores[$keyA] ?? null;
        $valB = $this->scores[$keyB] ?? null;

        $winnerName = $manualWinner;

        // Si les scores sont renseignés, on valide et détermine le vainqueur automatiquement
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
            $bracket = app(RecordMatchResultAction::class)->execute(
                $this->participants,
                $this->results,
                $round,
                $position,
                $winnerName,
            );
        } catch (InvalidMatchResultException $e) {
            $this->error = $e->getMessage();
            return;
        }

        $this->results[] = [
            'round' => $round,
            'position' => $position,
            'winner' => $winnerName,
            'score_a' => is_numeric($valA) ? (int) $valA : null,
            'score_b' => is_numeric($valB) ? (int) $valB : null,
        ];

        unset($this->bracket);

        if ($bracket->isComplete()) {
            $this->champion = $bracket->champion()?->name;

            app(HistoryStore::class)->push(GameModeType::BRACKET, [
                'champion' => $this->champion,
                'participants' => $this->participants,
            ]);
        }
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
    }

    public function canStart(): bool
    {
        return count($this->participants) >= self::MIN_PARTICIPANTS;
    }

    #[Computed]
    public function bracket(): Bracket
    {
        return app(RebuildBracketAction::class)->execute($this->participants, $this->results);
    }

    public function render()
    {
        $mode = GameModeType::BRACKET->toDto();

        return view('livewire.bracket.bracket-page')
            ->layout('layouts.app', [
                'title' => $mode->metaTitle,
                'metaDescription' => $mode->metaDescription,
            ]);
    }
}
