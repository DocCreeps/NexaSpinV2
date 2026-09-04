<?php

use App\Application\History\HistoryStore;
use App\Application\Home\Enums\GameModeType;
use App\Application\Roulette\BankrollStore;
use App\Livewire\Roulette\NumberRoulettePage;
use Livewire\Livewire;

it('starts a new visitor with their starting bankroll and no bet history', function () {
    $startingAmount = app(BankrollStore::class)->startingAmount();

    Livewire::test(NumberRoulettePage::class)
        ->assertSet('bankroll', $startingAmount)
        ->assertSet('startingBankroll', $startingAmount)
        ->assertSet('history', []);
});

it('refuses to place a straight bet without a chosen number', function () {
    $component = Livewire::test(NumberRoulettePage::class)
        ->set('selectedBetType', 'straight')
        ->set('selectedBetNumber', null)
        ->call('addBet');

    expect($component->get('error'))->not->toBeNull()
        ->and($component->get('bets'))->toBe([]);
});

it('refuses a stake below the minimum', function () {
    $component = Livewire::test(NumberRoulettePage::class)
        ->set('stake', 0)
        ->call('addBet');

    expect($component->get('error'))->not->toBeNull()
        ->and($component->get('bets'))->toBe([]);
});

it('refuses a stake bigger than the current bankroll', function () {
    $startingAmount = app(BankrollStore::class)->startingAmount();

    $component = Livewire::test(NumberRoulettePage::class)
        ->set('stake', $startingAmount + 1000)
        ->call('addBet');

    expect($component->get('error'))->not->toBeNull()
        ->and($component->get('bets'))->toBe([]);
});

it('deducts the stake from the bankroll as soon as a bet is placed', function () {
    $component = Livewire::test(NumberRoulettePage::class)
        ->set('selectedBetType', 'red')
        ->set('stake', 100)
        ->call('addBet');

    $startingAmount = app(BankrollStore::class)->startingAmount();

    expect($component->get('bankroll'))->toBe($startingAmount - 100)
        ->and($component->get('bets'))->toHaveCount(1);
});

it('merges a second identical bet into the first instead of creating a duplicate', function () {
    $component = Livewire::test(NumberRoulettePage::class)
        ->set('selectedBetType', 'red')
        ->set('stake', 50)
        ->call('addBet')
        ->call('addBet');

    expect($component->get('bets'))->toHaveCount(1)
        ->and($component->get('bets')[0]['stake'])->toBe(100);
});

it('refunds the stake when a bet is removed', function () {
    $component = Livewire::test(NumberRoulettePage::class)
        ->set('selectedBetType', 'black')
        ->set('stake', 75)
        ->call('addBet');

    $bankrollAfterBet = $component->get('bankroll');

    $component->call('removeBet', 0);

    expect($component->get('bankroll'))->toBe($bankrollAfterBet + 75)
        ->and($component->get('bets'))->toBe([]);
});

it('refunds every bet when the table is cleared', function () {
    $startingAmount = app(BankrollStore::class)->startingAmount();

    $component = Livewire::test(NumberRoulettePage::class)
        ->set('selectedBetType', 'red')
        ->set('stake', 40)
        ->call('addBet')
        ->set('selectedBetType', 'black')
        ->call('addBet')
        ->call('clearBets');

    expect($component->get('bets'))->toBe([])
        ->and($component->get('bankroll'))->toBe($startingAmount);
});

it('refuses to spin without any bet on the table', function () {
    $component = Livewire::test(NumberRoulettePage::class)->call('spin');

    expect($component->get('error'))->not->toBeNull()
        ->and($component->get('spinning'))->toBeFalse();
});

it('marks the table as spinning and prepares a pending result once a bet is placed', function () {
    $component = Livewire::test(NumberRoulettePage::class)
        ->set('selectedBetType', 'red')
        ->set('stake', 50)
        ->call('addBet')
        ->call('spin');

    $component->assertDispatched('roulette-spin');

    expect($component->get('spinning'))->toBeTrue()
        ->and($component->get('pendingHistoryEntry'))->not->toBeNull()
        ->and($component->get('pendingHistoryEntry')['total_stake'])->toBe(50);
});

it('credits the bankroll by exactly the winnings when confirming a winning spin, and leaves it untouched otherwise', function () {
    $component = Livewire::test(NumberRoulettePage::class)
        ->set('selectedBetType', 'red')
        ->set('stake', 50)
        ->call('addBet')
        ->call('spin');

    $entry = $component->get('pendingHistoryEntry');
    $bankrollBeforeConfirm = $component->get('bankroll');

    $component->call('confirmSpin');

    $expectedBankroll = $entry['total_return'] > 0
        ? $bankrollBeforeConfirm + $entry['total_return']
        : $bankrollBeforeConfirm;

    expect($component->get('bankroll'))->toBe($expectedBankroll)
        ->and($component->get('spinning'))->toBeFalse()
        ->and($component->get('pendingHistoryEntry'))->toBeNull()
        ->and($component->get('bets'))->toBe([])
        ->and($component->get('lastResult'))->toBe($entry['result']);
});

it('records the confirmed spin in the shared history store', function () {
    Livewire::test(NumberRoulettePage::class)
        ->set('selectedBetType', 'red')
        ->set('stake', 50)
        ->call('addBet')
        ->call('spin')
        ->call('confirmSpin');

    expect(app(HistoryStore::class)->all(GameModeType::NUMBER_ROULETTE))->toHaveCount(1);
});

it('clears both the visible history and the persisted store', function () {
    $component = Livewire::test(NumberRoulettePage::class)
        ->set('selectedBetType', 'red')
        ->set('stake', 50)
        ->call('addBet')
        ->call('spin')
        ->call('confirmSpin')
        ->call('clearHistory');

    expect($component->get('history'))->toBe([])
        ->and(app(HistoryStore::class)->all(GameModeType::NUMBER_ROULETTE))->toBe([]);
});

it('restores the starting bankroll and clears the table on reset', function () {
    $startingAmount = app(BankrollStore::class)->startingAmount();

    $component = Livewire::test(NumberRoulettePage::class)
        ->set('selectedBetType', 'red')
        ->set('stake', 50)
        ->call('addBet')
        ->call('resetBankroll');

    expect($component->get('bankroll'))->toBe($startingAmount)
        ->and($component->get('bets'))->toBe([]);
});

it('can only spin when there is at least one bet and the wheel is not already spinning', function () {
    $component = Livewire::test(NumberRoulettePage::class);

    expect($component->instance()->canSpin())->toBeFalse();

    $component->set('selectedBetType', 'red')->set('stake', 50)->call('addBet');

    expect($component->instance()->canSpin())->toBeTrue();
});
