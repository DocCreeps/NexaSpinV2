<?php

namespace App\Livewire\Teams;

use App\Application\History\HistoryStore;
use App\Application\Home\Enums\GameModeType;
use App\Application\Teams\TeamsGenerator;
use App\Livewire\Draw\Concerns\ManagesParticipants;
use Livewire\Component;

/**
 * Page de tirage par équipes : répartit les participants en N équipes aléatoires
 * et de taille égale. Les participants excédentaires deviennent des remplaçants.
 */
class TeamsPage extends Component
{
    use ManagesParticipants;

    private const MIN_PARTICIPANTS = 4;
    private const MIN_TEAMS = 2;
    private const MAX_TEAMS = 20;
    private const MAX_HISTORY = 50;

    public int $teamsCount = 2;

    /** @var array<int, array<int, string>> */
    public array $teams = [];

    /** @var array<int, string> */
    public array $substitutes = [];

    public bool $hasResult = false;

    /**
     * @var array<int, array{teams_count: int, teams: array<int, array<int, string>>, substitutes: array<int, string>, participants: array<int, string>}>
     */
    public array $history = [];

    public function mount(HistoryStore $historyStore): void
    {
        $this->history = $historyStore->all(GameModeType::TEAMS);
    }

    public function clearHistory(): void
    {
        $this->history = [];

        app(HistoryStore::class)->clear(GameModeType::TEAMS);
    }

    protected function afterParticipantsChanged(): void
    {
        $this->hasResult = false;
        $this->teams = [];
        $this->substitutes = [];
    }

    public function incrementTeamsCount(): void
    {
        $this->teamsCount = min($this->teamsCount + 1, self::MAX_TEAMS);
    }

    public function decrementTeamsCount(): void
    {
        $this->teamsCount = max($this->teamsCount - 1, self::MIN_TEAMS);
    }

    public function draw(TeamsGenerator $generator): void
    {
        $this->error = null;

        if (count($this->participants) < self::MIN_PARTICIPANTS) {
            $this->error = sprintf('Ajoutez au moins %d participants.', self::MIN_PARTICIPANTS);

            return;
        }

        if ($this->teamsCount < self::MIN_TEAMS) {
            $this->error = sprintf('Choisissez au moins %d équipes.', self::MIN_TEAMS);

            return;
        }

        if ($this->teamsCount > count($this->participants)) {
            $this->error = 'Il ne peut pas y avoir plus d’équipes que de participants.';

            return;
        }

        $result = $generator->generate($this->participants, $this->teamsCount);

        $this->teams = $result['teams'];
        $this->substitutes = $result['substitutes'];
        $this->hasResult = true;

        $entry = [
            'teams_count' => $this->teamsCount,
            'teams' => $this->teams,
            'substitutes' => $this->substitutes,
            'participants' => $this->participants,
        ];

        $this->history[] = $entry;

        if (count($this->history) > self::MAX_HISTORY) {
            $this->history = array_slice($this->history, -self::MAX_HISTORY);
        }

        app(HistoryStore::class)->push(GameModeType::TEAMS, $entry);
    }

    public function canDraw(): bool
    {
        return count($this->participants) >= self::MIN_PARTICIPANTS
            && $this->teamsCount >= self::MIN_TEAMS
            && $this->teamsCount <= count($this->participants);
    }

    public function render()
    {
        $mode = GameModeType::TEAMS->toDto();

        return view('livewire.teams.teams-page')
            ->layout('layouts.app', [
                'title' => $mode->metaTitle,
                'metaDescription' => $mode->metaDescription,
            ]);
    }
}
