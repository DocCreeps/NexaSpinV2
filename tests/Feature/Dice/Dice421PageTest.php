<?php

use App\Application\History\HistoryStore;
use App\Application\Home\Enums\GameModeType;
use App\Livewire\Dice\Dice421Page;
use Livewire\Livewire;

it('starts a fresh game with three unrolled dice and no throws', function () {
    Livewire::test(Dice421Page::class)
        ->assertSet('dice', [1, 1, 1])
        ->assertSet('kept', [false, false, false])
        ->assertSet('throwCount', 0)
        ->assertSet('isOver', false);
});

it('always ends the game within the maximum number of throws, whether it is won or not', function () {
    $component = Livewire::test(Dice421Page::class);

    for ($i = 0; $i < $component->instance()->maxThrows() && ! $component->get('isOver'); $i++) {
        $component->call('roll');
    }

    expect($component->get('isOver'))->toBeTrue()
        ->and($component->get('throwCount'))->toBeLessThanOrEqual($component->instance()->maxThrows());
});

it('refuses to roll once the game is already over', function () {
    $component = Livewire::test(Dice421Page::class);

    for ($i = 0; $i < $component->instance()->maxThrows(); $i++) {
        $component->call('roll');
    }

    $throwCountAtEnd = $component->get('throwCount');

    $component->call('roll');

    expect($component->get('error'))->not->toBeNull()
        ->and($component->get('throwCount'))->toBe($throwCountAtEnd);
});

it('only prepares a pending history entry once the game is over, not after every throw', function () {
    $component = Livewire::test(Dice421Page::class)->call('roll');

    if (! $component->get('isOver')) {
        expect($component->get('pendingHistoryEntry'))->toBeNull();
    } else {
        expect($component->get('pendingHistoryEntry'))->not->toBeNull();
    }
});

it('does not add anything to history until finalizeRoll is called', function () {
    $component = Livewire::test(Dice421Page::class);

    for ($i = 0; $i < $component->instance()->maxThrows() && ! $component->get('isOver'); $i++) {
        $component->call('roll');
    }

    expect($component->get('history'))->toBe([])
        ->and(app(HistoryStore::class)->all(GameModeType::DICE_421))->toBe([]);
});

it('commits the finished game to history and the shared store once finalized', function () {
    $component = Livewire::test(Dice421Page::class);

    for ($i = 0; $i < $component->instance()->maxThrows() && ! $component->get('isOver'); $i++) {
        $component->call('roll');
    }

    $component->call('finalizeRoll');

    expect($component->get('history'))->toHaveCount(1)
        ->and(app(HistoryStore::class)->all(GameModeType::DICE_421))->toHaveCount(1);
});

it('toggles whether a die is kept for the next throw', function () {
    Livewire::test(Dice421Page::class)
        ->call('toggleKeep', 0)
        ->assertSet('kept', [true, false, false])
        ->call('toggleKeep', 0)
        ->assertSet('kept', [false, false, false]);
});

it('ignores an out-of-range die index', function () {
    Livewire::test(Dice421Page::class)
        ->call('toggleKeep', 99)
        ->assertSet('kept', [false, false, false]);
});

it('no longer allows keeping dice once the game is over', function () {
    $component = Livewire::test(Dice421Page::class);

    for ($i = 0; $i < $component->instance()->maxThrows() && ! $component->get('isOver'); $i++) {
        $component->call('roll');
    }

    $keptBefore = $component->get('kept');

    $component->call('toggleKeep', 0);

    expect($component->get('kept'))->toBe($keptBefore);
});

it('starts a brand new game on resetGame, clearing the previous outcome', function () {
    $component = Livewire::test(Dice421Page::class);

    for ($i = 0; $i < $component->instance()->maxThrows() && ! $component->get('isOver'); $i++) {
        $component->call('roll');
    }

    $component->call('resetGame');

    expect($component->get('dice'))->toBe([1, 1, 1])
        ->and($component->get('throwCount'))->toBe(0)
        ->and($component->get('isOver'))->toBeFalse()
        ->and($component->get('pendingHistoryEntry'))->toBeNull();
});

it('counts only the won games in the history', function () {
    $component = Livewire::test(Dice421Page::class);

    // Seed history directly: winCount() is pure accounting logic, independent
    // of the randomness of an actual roll.
    $component->set('history', [
        ['dice' => [4, 2, 1], 'throws' => 1, 'won' => true, 'combination' => '4-2-1'],
        ['dice' => [5, 5, 5], 'throws' => 3, 'won' => false, 'combination' => 'Brelan'],
        ['dice' => [4, 2, 1], 'throws' => 2, 'won' => true, 'combination' => '4-2-1'],
    ]);

    expect($component->instance()->winCount())->toBe(2);
});

it('clears both the visible history and the persisted store', function () {
    $component = Livewire::test(Dice421Page::class);

    for ($i = 0; $i < $component->instance()->maxThrows() && ! $component->get('isOver'); $i++) {
        $component->call('roll');
    }

    $component->call('finalizeRoll')->call('clearHistory');

    expect($component->get('history'))->toBe([])
        ->and(app(HistoryStore::class)->all(GameModeType::DICE_421))->toBe([]);
});
