<?php

use App\Livewire\Dice\Dice421Page;
use Livewire\Livewire;

// --- État initial -----------------------------------------------------------------

it('mounts with a fresh game state', function () {
    Livewire::test(Dice421Page::class)
        ->assertSet('dice', [1, 1, 1])
        ->assertSet('kept', [false, false, false])
        ->assertSet('throwCount', 0)
        ->assertSet('isOver', false)
        ->assertSet('isWon', false)
        ->assertSet('combinationLabel', null)
        ->assertSet('error', null)
        ->assertSet('history', []);
});

it('exposes the max throws allowed by the underlying strategy', function () {
    expect(Livewire::test(Dice421Page::class)->instance()->maxThrows())->toBe(3);
});

// --- Lancer de dés ------------------------------------------------------------------

it('rolls the dice and increments the throw count', function () {
    Livewire::test(Dice421Page::class)
        ->call('roll')
        ->assertSet('throwCount', 1)
        ->assertDispatched('dice-rolled');
});

it('dispatches dice-rolled with the current game state', function () {
    Livewire::test(Dice421Page::class)
        ->call('roll')
        ->assertDispatched(
            'dice-rolled',
            throwCount: 1,
        );
});

it('ends the game once the maximum number of throws is reached', function () {
    Livewire::test(Dice421Page::class)
        ->call('roll')
        ->call('roll')
        ->call('roll')
        ->assertSet('throwCount', 3)
        ->assertSet('isOver', true);
});

it('refuses to roll once the game is already over and sets an error', function () {
    $component = Livewire::test(Dice421Page::class)
        ->call('roll')
        ->call('roll')
        ->call('roll');

    expect($component->get('isOver'))->toBeTrue();

    $component->call('roll')
        ->assertSet('throwCount', 3) // ne progresse plus
        ->assertSet('error', fn (?string $error) => $error !== null)
        ->assertDispatched('dice-rolled');
});

it('records a history entry once the game is over', function () {
    Livewire::test(Dice421Page::class)
        ->call('roll')
        ->call('roll')
        ->call('roll')
        ->assertSet('history', fn (array $history) => count($history) === 1);
});

it('does not record a history entry while the game is still in progress', function () {
    Livewire::test(Dice421Page::class)
        ->call('roll')
        ->assertSet('history', []);
});

it('caps the history to the last 100 entries', function () {
    Livewire::test(Dice421Page::class)
        ->set('history', array_fill(0, 100, [
            'dice' => [1, 1, 1],
            'throws' => 3,
            'won' => false,
            'combination' => 'Brelan',
        ]))
        ->call('roll')
        ->call('roll')
        ->call('roll')
        ->assertSet('history', fn (array $history) => count($history) === 100);
});

// --- Conservation des dés (toggleKeep) -----------------------------------------------

it('toggles a die kept state by index', function () {
    Livewire::test(Dice421Page::class)
        ->call('toggleKeep', 0)
        ->assertSet('kept', [true, false, false])
        ->call('toggleKeep', 0)
        ->assertSet('kept', [false, false, false]);
});

it('ignores an out-of-range index when toggling a kept die', function () {
    Livewire::test(Dice421Page::class)
        ->call('toggleKeep', 5)
        ->assertSet('kept', [false, false, false]);
});

it('ignores negative indexes when toggling a kept die', function () {
    Livewire::test(Dice421Page::class)
        ->call('toggleKeep', -1)
        ->assertSet('kept', [false, false, false]);
});

it('does not allow toggling a kept die once the game is over', function () {
    Livewire::test(Dice421Page::class)
        ->call('roll')
        ->call('roll')
        ->call('roll')
        ->call('toggleKeep', 0)
        ->assertSet('kept', [false, false, false]);
});

// --- Réinitialisation -----------------------------------------------------------------

it('resets the game state and dispatches dice-reset', function () {
    Livewire::test(Dice421Page::class)
        ->call('roll')
        ->call('roll')
        ->call('roll')
        ->call('toggleKeep', 0)
        ->call('resetGame')
        ->assertSet('dice', [1, 1, 1])
        ->assertSet('kept', [false, false, false])
        ->assertSet('throwCount', 0)
        ->assertSet('isOver', false)
        ->assertSet('isWon', false)
        ->assertSet('combinationLabel', null)
        ->assertSet('error', null)
        ->assertDispatched('dice-reset');
});

it('keeps the history across a reset', function () {
    Livewire::test(Dice421Page::class)
        ->call('roll')
        ->call('roll')
        ->call('roll')
        ->call('resetGame')
        ->assertSet('history', fn (array $history) => count($history) === 1);
});

// --- Statistiques ------------------------------------------------------------------------

it('computes the win count from the history', function () {
    $component = Livewire::test(Dice421Page::class)
        ->set('history', [
            ['dice' => [4, 2, 1], 'throws' => 1, 'won' => true, 'combination' => '421'],
            ['dice' => [1, 3, 6], 'throws' => 3, 'won' => false, 'combination' => 'Aucune combinaison'],
            ['dice' => [4, 2, 1], 'throws' => 2, 'won' => true, 'combination' => '421'],
        ]);

    expect($component->instance()->winCount())->toBe(2);
});

it('returns zero wins when the history is empty', function () {
    expect(Livewire::test(Dice421Page::class)->instance()->winCount())->toBe(0);
});
