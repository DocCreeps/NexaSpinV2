<?php

use App\Application\History\HistoryStore;
use App\Application\Home\Enums\GameModeType;
use App\Livewire\Tombola\TombolaPage;
use Livewire\Livewire;

function addTombolaParticipants($component, array $names)
{
    foreach ($names as $name) {
        $component->set('participant', $name)->call('addParticipant');
    }

    return $component;
}

it('refuses to start with fewer than 3 participants', function () {
    $component = addTombolaParticipants(Livewire::test(TombolaPage::class), ['A', 'B'])
        ->call('start');

    expect($component->get('error'))->not->toBeNull()
        ->and($component->get('drawing'))->toBeFalse();
});

it('refuses more lots than participants without replacement', function () {
    $component = addTombolaParticipants(Livewire::test(TombolaPage::class), ['A', 'B', 'C'])
        ->set('lotsCount', 5)
        ->set('allowDuplicates', false)
        ->call('start');

    expect($component->get('error'))->not->toBeNull()
        ->and($component->get('drawing'))->toBeFalse();
});

it('draws every lot instantly when slow mode is off', function () {
    $component = addTombolaParticipants(Livewire::test(TombolaPage::class), ['A', 'B', 'C', 'D', 'E'])
        ->set('lotsCount', 3)
        ->set('slowMode', false)
        ->call('start');

    expect($component->get('drawing'))->toBeFalse()
        ->and($component->get('winners'))->toHaveCount(3);
});

it('reveals the lots one step at a time in slow mode until the requested count is reached', function () {
    $component = addTombolaParticipants(Livewire::test(TombolaPage::class), ['A', 'B', 'C', 'D', 'E'])
        ->set('lotsCount', 3)
        ->set('slowMode', true)
        ->call('start');

    while ($component->get('drawing')) {
        $component->call('drawNextStep');
    }

    expect($component->get('winners'))->toHaveCount(3);
});

it('never draws the same name twice without replacement', function () {
    $component = addTombolaParticipants(Livewire::test(TombolaPage::class), ['A', 'B', 'C', 'D', 'E'])
        ->set('lotsCount', 5)
        ->set('allowDuplicates', false)
        ->set('slowMode', false)
        ->call('start');

    $winners = $component->get('winners');

    expect($winners)->toHaveCount(5)
        ->and(array_unique($winners))->toHaveCount(5);
});

it('drains the entire pool one participant at a time in one-by-one mode, ignoring the lots count', function () {
    $names = ['A', 'B', 'C', 'D', 'E'];
    $component = addTombolaParticipants(Livewire::test(TombolaPage::class), $names)
        ->call('setDrawMode', 'one_by_one')
        ->call('start');

    expect($component->get('drawing'))->toBeTrue()
        ->and($component->get('winners'))->toBe([]);

    while ($component->get('drawing')) {
        $component->call('drawNext');
    }

    expect($component->get('winners'))->toHaveCount(count($names))
        ->and(array_unique($component->get('winners')))->toHaveCount(count($names));
});

it('can draw the same participant more than once when duplicates are allowed and their weight allows it', function () {
    // With a heavily boosted weight and duplicates allowed, the same participant
    // should be able to win more than one lot across enough attempts.
    $duplicateObserved = false;

    for ($attempt = 0; $attempt < 15 && ! $duplicateObserved; $attempt++) {
        $component = addTombolaParticipants(Livewire::test(TombolaPage::class), ['A', 'B', 'C']);
        $component->call('updateParticipantWeight', 0, 20);
        $component->set('allowDuplicates', true)
            ->set('lotsCount', 6)
            ->set('slowMode', false)
            ->call('start');

        $winners = $component->get('winners');
        if (count($winners) !== count(array_unique($winners))) {
            $duplicateObserved = true;
        }
    }

    expect($duplicateObserved)->toBeTrue();
});

it('records the finished draw in the shared history store', function () {
    addTombolaParticipants(Livewire::test(TombolaPage::class), ['A', 'B', 'C', 'D'])
        ->set('lotsCount', 2)
        ->set('slowMode', false)
        ->call('start');

    expect(app(HistoryStore::class)->all(GameModeType::TOMBOLA))->toHaveCount(1);
});

it('resets the draw whenever the participant list changes', function () {
    $component = addTombolaParticipants(Livewire::test(TombolaPage::class), ['A', 'B', 'C', 'D'])
        ->set('lotsCount', 2)
        ->set('slowMode', false)
        ->call('start');

    expect($component->get('winners'))->toHaveCount(2);

    $component->set('participant', 'E')->call('addParticipant');

    expect($component->get('winners'))->toBe([]);
});

it('clears the history without disturbing an ongoing configuration', function () {
    $component = addTombolaParticipants(Livewire::test(TombolaPage::class), ['A', 'B', 'C', 'D'])
        ->set('lotsCount', 2)
        ->set('slowMode', false)
        ->call('start')
        ->call('clearHistory');

    expect($component->get('history'))->toBe([])
        ->and(app(HistoryStore::class)->all(GameModeType::TOMBOLA))->toBe([]);
});

it('reports whether a draw can currently be started', function () {
    $component = Livewire::test(TombolaPage::class);

    expect($component->instance()->canStart())->toBeFalse();

    addTombolaParticipants($component, ['A', 'B', 'C']);

    expect($component->instance()->canStart())->toBeTrue();
});
