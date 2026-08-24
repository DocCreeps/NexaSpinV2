<?php

namespace App\Livewire\Teams;

use App\Application\History\HistoryStore;
use App\Application\Home\Enums\GameModeType;
use App\Application\Teams\TeamsGenerator;
use App\Livewire\Draw\Concerns\ManagesParticipants;
use Livewire\Attributes\Computed;
use Livewire\Component;

class TeamsPage extends Component
{
    use ManagesParticipants;

    private const MIN_PARTICIPANTS = 4;
    private const MIN_TEAMS = 2;
    private const MAX_TEAMS = 20;
    private const MAX_HISTORY = 50;

    public int $teamsCount = 2;

    /** @var array<int, array<int, string>> */
    public array $fullTeams = [];

    /** @var array<string, int> */
    public array $fullSubstitutes = [];

    /** @var array<int, array<int, string>> */
    public array $teams = [];

    /** @var array<string, int> */
    public array $substitutes = [];

    public bool $hasResult = false;
    public bool $drawing = false;
    public bool $slowMode = true;
    public bool $autoAdvance = true;

    /** @var array<int, array{player: string, type: 'team'|'substitute', team_index: int}> */
    public array $stepsSequence = [];
    public int $currentStepIndex = 0;

    /**
     * @var array<int, array{teams_count: int, teams: array<int, array<int, string>>, substitutes: array<string, int>, participants: array<int, string>}>
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
        $this->stop();
    }

    public function toggleSlowMode(): void
    {
        if ($this->drawing) {
            return;
        }

        $this->slowMode = ! $this->slowMode;
    }

    public function incrementTeamsCount(): void
    {
        if ($this->drawing) {
            return;
        }

        $this->teamsCount = min($this->teamsCount + 1, self::MAX_TEAMS);
    }

    public function decrementTeamsCount(): void
    {
        if ($this->drawing) {
            return;
        }

        $this->teamsCount = max($this->teamsCount - 1, self::MIN_TEAMS);
    }

    #[Computed]
    public function formattedTeams(): array
    {
        $formatted = [];

        for ($i = 0; $i < $this->teamsCount; $i++) {
            $members = $this->teams[$i] ?? [];

            $teamSubs = array_keys(
                array_filter($this->substitutes ?? [], fn($teamIdx) => $teamIdx === $i)
            );

            $formatted[] = [
                'index' => $i + 1,
                'members' => $members,
                'substitutes' => $teamSubs,
            ];
        }

        return $formatted;
    }

    public function start(TeamsGenerator $generator): void
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
        $rawTeams = $result['teams'];
        $rawSubstitutes = $result['substitutes'] ?? [];

        $this->fullTeams = [];
        foreach ($rawTeams as $index => $members) {
            $this->fullTeams[$index] = $members;
        }

        $this->fullSubstitutes = [];
        if (! empty($rawSubstitutes)) {
            $subList = $rawSubstitutes;
            shuffle($subList);
            $teamIndexes = range(0, $this->teamsCount - 1);
            shuffle($teamIndexes);

            foreach ($subList as $i => $subName) {
                $assignedTeam = $teamIndexes[$i % count($teamIndexes)];
                $this->fullSubstitutes[$subName] = $assignedTeam;
            }
        }

        $this->stepsSequence = [];
        foreach ($this->fullTeams as $teamIndex => $members) {
            foreach ($members as $member) {
                $this->stepsSequence[] = [
                    'player' => $member,
                    'type' => 'team',
                    'team_index' => $teamIndex,
                ];
            }
        }
        foreach ($this->fullSubstitutes as $subName => $teamIndex) {
            $this->stepsSequence[] = [
                'player' => $subName,
                'type' => 'substitute',
                'team_index' => $teamIndex,
            ];
        }

        shuffle($this->stepsSequence);

        $this->teams = array_fill(0, $this->teamsCount, []);
        $this->substitutes = [];
        $this->currentStepIndex = 0;
        $this->hasResult = true;

        if ($this->slowMode) {
            $this->drawing = true;
        } else {
            $this->teams = $this->fullTeams;
            $this->substitutes = $this->fullSubstitutes;
            $this->drawing = false;
            $this->saveToHistory();
        }
    }

    public function drawNextStep(): void
    {
        if (! $this->drawing || $this->currentStepIndex >= count($this->stepsSequence)) {
            return;
        }

        $step = $this->stepsSequence[$this->currentStepIndex];

        if ($step['type'] === 'team') {
            $this->teams[$step['team_index']][] = $step['player'];
        } else {
            $this->substitutes[$step['player']] = $step['team_index'];
        }

        $this->currentStepIndex++;

        if ($this->currentStepIndex >= count($this->stepsSequence)) {
            $this->drawing = false;
            $this->saveToHistory();
        }
    }

    public function stop(): void
    {
        $this->drawing = false;
        $this->hasResult = false;
        $this->teams = [];
        $this->substitutes = [];
        $this->fullTeams = [];
        $this->fullSubstitutes = [];
        $this->stepsSequence = [];
        $this->currentStepIndex = 0;
    }

    private function saveToHistory(): void
    {
        $entry = [
            'teams_count' => $this->teamsCount,
            'teams' => $this->fullTeams,
            'substitutes' => $this->fullSubstitutes,
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
