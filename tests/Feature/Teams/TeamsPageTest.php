<?php

use App\Application\History\HistoryStore;
use App\Application\Home\Enums\GameModeType;
use App\Livewire\Teams\TeamsPage;
use Livewire\Livewire;

function addTeamsParticipants($component, array $names)
{
    foreach ($names as $name) {
        $component->set('participant', $name)->call('addParticipant');
    }

    return $component;
}

it('refuses to start with fewer than 4 participants', function () {
    $component = addTeamsParticipants(Livewire::test(TeamsPage::class), ['A', 'B', 'C'])
        ->call('start');

    expect($component->get('error'))->not->toBeNull()
        ->and($component->get('hasResult'))->toBeFalse();
});

it('refuses to start with fewer than 2 teams', function () {
    $component = addTeamsParticipants(Livewire::test(TeamsPage::class), ['A', 'B', 'C', 'D'])
        ->set('teamsCount', 1)
        ->call('start');

    expect($component->get('error'))->not->toBeNull()
        ->and($component->get('hasResult'))->toBeFalse();
});

it('refuses to start with more teams than participants', function () {
    $component = addTeamsParticipants(Livewire::test(TeamsPage::class), ['A', 'B', 'C', 'D'])
        ->set('teamsCount', 5)
        ->call('start');

    expect($component->get('error'))->not->toBeNull()
        ->and($component->get('hasResult'))->toBeFalse();
});

it('draws the teams instantly when slow mode is off', function () {
    $component = addTeamsParticipants(Livewire::test(TeamsPage::class), ['A', 'B', 'C', 'D', 'E', 'F'])
        ->set('teamsCount', 2)
        ->set('slowMode', false)
        ->call('start');

    expect($component->get('drawing'))->toBeFalse()
        ->and($component->get('hasResult'))->toBeTrue()
        ->and($component->get('teams'))->toHaveCount(2);
});

it('does not fill the teams immediately in slow mode, only after every step is drawn', function () {
    $names = ['A', 'B', 'C', 'D', 'E', 'F'];
    $component = addTeamsParticipants(Livewire::test(TeamsPage::class), $names)
        ->set('teamsCount', 2)
        ->set('slowMode', true)
        ->call('start');

    expect($component->get('drawing'))->toBeTrue();

    // Drain every remaining step exactly once per participant.
    while ($component->get('drawing')) {
        $component->call('drawNextStep');
    }

    expect($component->get('drawing'))->toBeFalse()
        ->and($component->get('hasResult'))->toBeTrue();
});

it('never loses or duplicates a participant across teams and substitutes', function () {
    $names = ['A', 'B', 'C', 'D', 'E', 'F', 'G'];
    $component = addTeamsParticipants(Livewire::test(TeamsPage::class), $names)
        ->set('teamsCount', 3)
        ->set('slowMode', false)
        ->call('start');

    $everyone = array_merge(array_merge(...$component->get('teams')), array_keys($component->get('substitutes')));
    sort($everyone);
    $expected = $names;
    sort($expected);

    expect($everyone)->toBe($expected);
});

it('resets the whole draw (but not the participant list) when a new draw is stopped', function () {
    $names = ['A', 'B', 'C', 'D'];
    $component = addTeamsParticipants(Livewire::test(TeamsPage::class), $names)
        ->set('teamsCount', 2)
        ->set('slowMode', false)
        ->call('start')
        ->call('stop');

    expect($component->get('hasResult'))->toBeFalse()
        ->and($component->get('teams'))->toBe([])
        ->and($component->get('participants'))->toBe($names);
});

it('restarts the draw automatically whenever the participant list changes', function () {
    $names = ['A', 'B', 'C', 'D'];
    $component = addTeamsParticipants(Livewire::test(TeamsPage::class), $names)
        ->set('teamsCount', 2)
        ->set('slowMode', false)
        ->call('start');

    expect($component->get('hasResult'))->toBeTrue();

    $component->set('participant', 'E')->call('addParticipant');

    expect($component->get('hasResult'))->toBeFalse();
});

it('records a completed draw in the shared history store', function () {
    addTeamsParticipants(Livewire::test(TeamsPage::class), ['A', 'B', 'C', 'D'])
        ->set('teamsCount', 2)
        ->set('slowMode', false)
        ->call('start');

    expect(app(HistoryStore::class)->all(GameModeType::TEAMS))->toHaveCount(1);
});

it('clears the history without disturbing the current draw', function () {
    $component = addTeamsParticipants(Livewire::test(TeamsPage::class), ['A', 'B', 'C', 'D'])
        ->set('teamsCount', 2)
        ->set('slowMode', false)
        ->call('start')
        ->call('clearHistory');

    expect($component->get('history'))->toBe([])
        ->and(app(HistoryStore::class)->all(GameModeType::TEAMS))->toBe([])
        ->and($component->get('hasResult'))->toBeTrue();
});

it('keeps the teams count within the allowed [2, 20] bounds', function () {
    $component = Livewire::test(TeamsPage::class)
        ->set('teamsCount', 20)
        ->call('incrementTeamsCount');

    expect($component->get('teamsCount'))->toBe(20);

    $component->set('teamsCount', 2)->call('decrementTeamsCount');

    expect($component->get('teamsCount'))->toBe(2);
});

it('reports whether a draw can currently be launched', function () {
    $component = Livewire::test(TeamsPage::class);

    expect($component->instance()->canDraw())->toBeFalse();

    addTeamsParticipants($component, ['A', 'B', 'C', 'D'])->set('teamsCount', 2);

    expect($component->instance()->canDraw())->toBeTrue();
});
