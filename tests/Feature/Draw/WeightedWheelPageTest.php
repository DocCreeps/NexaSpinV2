<?php

use App\Application\History\HistoryStore;
use App\Application\Home\Enums\GameModeType;
use App\Livewire\Draw\WeightedWheelPage;
use Livewire\Livewire;

it('refuses to draw with fewer than 3 participants', function () {
    $component = fillWheelParticipants(Livewire::test(WeightedWheelPage::class), ['Alice', 'Bob'])
        ->call('draw');

    expect($component->get('error'))->not->toBeNull()
        ->and($component->get('result'))->toBeNull();
});

it('picks a winner among the participants once there are enough of them', function () {
    $component = fillWheelParticipants(Livewire::test(WeightedWheelPage::class), ['Alice', 'Bob', 'Charlie'])
        ->call('draw');

    expect(['Alice', 'Bob', 'Charlie'])->toContain($component->get('result'));
});

it('does not reveal the draw in history until confirmDraw is called', function () {
    $component = fillWheelParticipants(Livewire::test(WeightedWheelPage::class), ['Alice', 'Bob', 'Charlie'])
        ->call('draw');

    expect($component->get('history'))->toBe([]);

    $component->call('confirmDraw');

    expect($component->get('history'))->toHaveCount(1)
        ->and($component->get('history')[0]['winner'])->toBe($component->get('result'));
});

it('records the exact weights that were in effect at draw time', function () {
    $component = fillWheelParticipants(Livewire::test(WeightedWheelPage::class), ['Alice', 'Bob', 'Charlie'])
        ->call('updateParticipantWeight', 0, 40)
        ->call('draw')
        ->call('confirmDraw');

    expect($component->get('history')[0]['weights']['Alice'])->toBe(40);
});

it('persists the confirmed history in the shared store', function () {
    fillWheelParticipants(Livewire::test(WeightedWheelPage::class), ['Alice', 'Bob', 'Charlie'])
        ->call('draw')
        ->call('confirmDraw');

    expect(app(HistoryStore::class)->all(GameModeType::WEIGHTED))->toHaveCount(1);
});

it('clears both the in-memory and the persisted history', function () {
    $component = fillWheelParticipants(Livewire::test(WeightedWheelPage::class), ['Alice', 'Bob', 'Charlie'])
        ->call('draw')
        ->call('confirmDraw')
        ->call('clearHistory');

    expect($component->get('history'))->toBe([])
        ->and(app(HistoryStore::class)->all(GameModeType::WEIGHTED))->toBe([]);
});

it('resets the previous result whenever the participant list changes', function () {
    $component = fillWheelParticipants(Livewire::test(WeightedWheelPage::class), ['Alice', 'Bob', 'Charlie'])
        ->call('draw');

    expect($component->get('result'))->not->toBeNull();

    $component->set('participant', 'Dana')->call('addParticipant');

    expect($component->get('result'))->toBeNull();
});

it('draws a heavily weighted participant far more often than a lightly weighted one', function () {
    $wins = ['Rare' => 0, 'Common' => 0];

    for ($i = 0; $i < 60; $i++) {
        $component = Livewire::test(WeightedWheelPage::class)
            ->set('participant', 'Rare')->call('addParticipant')
            ->set('participant', 'Common')->call('addParticipant')
            ->set('participant', 'Filler')->call('addParticipant')
            ->call('updateParticipantWeight', 0, 1)
            ->call('updateParticipantWeight', 1, 50)
            ->call('draw');

        $winner = $component->get('result');

        if (isset($wins[$winner])) {
            $wins[$winner]++;
        }
    }

    expect($wins['Common'])->toBeGreaterThan($wins['Rare']);
});
