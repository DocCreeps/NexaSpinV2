<?php

use App\Application\History\HistoryStore;
use App\Application\Home\Enums\GameModeType;
use App\Livewire\Draw\WheelPage;
use Livewire\Livewire;

function fillWheelParticipants($component, array $names)
{
    foreach ($names as $name) {
        $component->set('participant', $name)->call('addParticipant');
    }

    return $component;
}

it('cannot be drawn with too few participants', function () {
    $component = fillWheelParticipants(Livewire::test(WheelPage::class), ['Alice', 'Bob']);

    expect($component->instance()->canDraw())->toBeFalse();

    $component->call('draw')
        ->assertSet('result', null)
        ->assertSet('error', fn (?string $error) => $error !== null);
});

it('can be drawn once enough participants are added', function () {
    $component = fillWheelParticipants(Livewire::test(WheelPage::class), ['Alice', 'Bob', 'Charlie']);

    expect($component->instance()->canDraw())->toBeTrue();

    $component->call('draw')
        ->assertSet('error', null)
        ->assertSet('result', fn (?string $winner) => in_array($winner, ['Alice', 'Bob', 'Charlie'], true));
});

it('does not add the result to the history until the animation is confirmed', function () {
    $component = fillWheelParticipants(Livewire::test(WheelPage::class), ['Alice', 'Bob', 'Charlie']);

    $component->call('draw')
        ->assertSet('history', []); // pas encore confirmé côté client

    $component->call('confirmDraw')
        ->assertSet('history', fn (array $history) => count($history) === 1);
});

it('persists a confirmed draw across page reloads (same visitor session)', function () {
    fillWheelParticipants(Livewire::test(WheelPage::class), ['Alice', 'Bob', 'Charlie'])
        ->call('draw')
        ->call('confirmDraw');

    // Un nouveau montage du composant doit relire l'historique depuis le cache.
    $fresh = Livewire::test(WheelPage::class);
    $fresh->assertSet('history', fn (array $history) => count($history) === 1);
});

it('clears both the in-memory history and the cached history', function () {
    fillWheelParticipants(Livewire::test(WheelPage::class), ['Alice', 'Bob', 'Charlie'])
        ->call('draw')
        ->call('confirmDraw')
        ->call('clearHistory')
        ->assertSet('history', []);

    expect(app(HistoryStore::class)->all(GameModeType::CLASSIC))->toBe([]);
});

it('resets the previous result whenever the participant list changes', function () {
    $component = fillWheelParticipants(Livewire::test(WheelPage::class), ['Alice', 'Bob', 'Charlie']);

    $component->call('draw')->assertSet('result', fn (?string $r) => $r !== null);

    $component->set('participant', 'Dana')->call('addParticipant')
        ->assertSet('result', null);
});
