<?php

use App\Application\History\HistoryStore;
use App\Application\Home\Enums\GameModeType;
use App\Livewire\Draw\EliminationWheelPage;
use Livewire\Livewire;

/**
 * Plays a full elimination tournament to its conclusion by repeatedly invoking
 * the same action the UI does on every click (handleAction), followed by the
 * confirmation the frontend sends once its animation ends. Who gets eliminated
 * at each step is genuinely random, but the number of steps required (participant
 * count minus one) is not, so this always terminates.
 */
function playElimination($component, int $participantCount)
{
    for ($i = 0; $i < $participantCount - 1; $i++) {
        $component->call('handleAction')->call('confirmElimination');
    }

    return $component;
}

it('refuses to start with fewer than 5 participants', function () {
    $component = fillWheelParticipants(Livewire::test(EliminationWheelPage::class), ['A', 'B', 'C', 'D'])
        ->call('start');

    expect($component->get('error'))->not->toBeNull()
        ->and($component->instance()->started())->toBeFalse();
});

it('locks the participant list as soon as the tournament has started', function () {
    $component = fillWheelParticipants(Livewire::test(EliminationWheelPage::class), ['A', 'B', 'C', 'D', 'E'])
        ->call('start');

    expect($component->instance()->started())->toBeTrue();

    $component->set('participant', 'F')->call('addParticipant');

    expect($component->get('participants'))->toHaveCount(5);
});

it('does not remove the eliminated player until the elimination is confirmed', function () {
    $component = fillWheelParticipants(Livewire::test(EliminationWheelPage::class), ['A', 'B', 'C', 'D', 'E'])
        ->call('handleAction');

    expect($component->get('participants'))->toHaveCount(5)
        ->and($component->get('pendingElimination'))->not->toBeNull()
        ->and($component->get('processing'))->toBeTrue();

    $component->call('confirmElimination');

    expect($component->get('participants'))->toHaveCount(4)
        ->and($component->get('eliminated'))->toHaveCount(1)
        ->and($component->get('processing'))->toBeFalse();
});

it('declares a winner only once a single participant remains', function () {
    $names = ['A', 'B', 'C', 'D', 'E'];
    $component = fillWheelParticipants(Livewire::test(EliminationWheelPage::class), $names);

    playElimination($component, count($names));

    expect($component->get('winner'))->not->toBeNull()
        ->and($names)->toContain($component->get('winner'))
        ->and($component->get('eliminated'))->toHaveCount(4);
});

it('prepares a pending tournament entry with every eliminated player, only once the winner is known', function () {
    $names = ['A', 'B', 'C', 'D', 'E'];
    $component = fillWheelParticipants(Livewire::test(EliminationWheelPage::class), $names);

    playElimination($component, count($names));

    $entry = $component->get('pendingTournamentEntry');

    expect($entry)->not->toBeNull()
        ->and($entry['winner'])->toBe($component->get('winner'))
        ->and($entry['participants'])->toBe($names)
        ->and($entry['eliminations'])->toHaveCount(4);
});

it('does not add the finished tournament to history until it is confirmed', function () {
    $names = ['A', 'B', 'C', 'D', 'E'];
    $component = fillWheelParticipants(Livewire::test(EliminationWheelPage::class), $names);

    playElimination($component, count($names));

    expect($component->get('history'))->toBe([]);

    $component->call('confirmTournamentHistory');

    expect($component->get('history'))->toHaveCount(1)
        ->and(app(HistoryStore::class)->all(GameModeType::ELIMINATION))->toHaveCount(1);
});

it('restarts the tournament with the original participant list untouched', function () {
    $names = ['A', 'B', 'C', 'D', 'E'];
    $component = fillWheelParticipants(Livewire::test(EliminationWheelPage::class), $names);

    playElimination($component, count($names));

    $component->call('restart');

    expect($component->get('winner'))->toBeNull()
        ->and($component->get('eliminated'))->toBe([])
        ->and($component->instance()->started())->toBeFalse()
        ->and($component->get('participants'))->toBe($names);
});

it('detects and recovers from a stuck spin', function () {
    $component = fillWheelParticipants(Livewire::test(EliminationWheelPage::class), ['A', 'B', 'C', 'D', 'E'])
        ->call('handleAction');

    expect($component->instance()->isStuck())->toBeFalse();

    // Simulate a spin that started 10 real seconds ago (well past the 8s threshold),
    // without needing to actually wait in the test.
    $component->set('processingStartedAt', microtime(true) - 10);

    expect($component->instance()->isStuck())->toBeTrue();

    $component->call('unstick');

    expect($component->get('processing'))->toBeFalse()
        ->and($component->get('pendingElimination'))->toBeNull()
        ->and($component->get('error'))->not->toBeNull();
});

it('clears history without disturbing an in-progress tournament', function () {
    $component = fillWheelParticipants(Livewire::test(EliminationWheelPage::class), ['A', 'B', 'C', 'D', 'E'])
        ->call('start')
        ->call('clearHistory');

    expect($component->get('history'))->toBe([])
        ->and($component->instance()->started())->toBeTrue();
});
