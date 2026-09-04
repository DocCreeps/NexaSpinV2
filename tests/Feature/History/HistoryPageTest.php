<?php

use App\Application\History\HistoryStore;
use App\Application\Home\Enums\GameModeType;
use App\Livewire\History\HistoryPage;
use Livewire\Livewire;

it('shows every mode merged together by default', function () {
    $store = app(HistoryStore::class);
    $store->push(GameModeType::CLASSIC, ['winner' => 'Alice', 'participants' => ['Alice', 'Bob']]);
    $store->push(GameModeType::COIN_FLIP, ['side' => 'pile']);

    $component = Livewire::test(HistoryPage::class);

    expect($component->instance()->entries())->toHaveCount(2);
});

it('shows only the entries of the selected mode', function () {
    $store = app(HistoryStore::class);
    $store->push(GameModeType::CLASSIC, ['winner' => 'Alice', 'participants' => ['Alice', 'Bob']]);
    $store->push(GameModeType::COIN_FLIP, ['side' => 'pile']);

    $component = Livewire::test(HistoryPage::class)
        ->call('setFilter', GameModeType::CLASSIC->value);

    $entries = $component->instance()->entries();

    expect($entries)->toHaveCount(1)
        ->and($entries[0]['mode'])->toBe(GameModeType::CLASSIC->value);
});

it('shows only the entries whose mode belongs to the selected category', function () {
    $store = app(HistoryStore::class);
    // CLASSIC belongs to the "wheel" category, COIN_FLIP to "other".
    $store->push(GameModeType::CLASSIC, ['winner' => 'Alice', 'participants' => ['Alice', 'Bob']]);
    $store->push(GameModeType::COIN_FLIP, ['side' => 'pile']);

    $component = Livewire::test(HistoryPage::class)
        ->call('setFilterType', 'category')
        ->call('setFilter', 'wheel');

    $entries = $component->instance()->entries();

    expect($entries)->toHaveCount(1)
        ->and($entries[0]['mode'])->toBe(GameModeType::CLASSIC->value);
});

it('resets the filter back to "all" when the filter type is switched', function () {
    Livewire::test(HistoryPage::class)
        ->call('setFilter', GameModeType::CLASSIC->value)
        ->call('setFilterType', 'category')
        ->assertSet('filter', 'all');
});

it('ignores an invalid filter type', function () {
    Livewire::test(HistoryPage::class)
        ->call('setFilterType', 'not-a-real-type')
        ->assertSet('filterType', 'mode');
});

it('clears only the currently filtered mode, leaving the others untouched', function () {
    $store = app(HistoryStore::class);
    $store->push(GameModeType::CLASSIC, ['winner' => 'Alice', 'participants' => ['Alice', 'Bob']]);
    $store->push(GameModeType::COIN_FLIP, ['side' => 'pile']);

    Livewire::test(HistoryPage::class)
        ->call('setFilter', GameModeType::CLASSIC->value)
        ->call('clear');

    expect($store->all(GameModeType::CLASSIC))->toBe([])
        ->and($store->all(GameModeType::COIN_FLIP))->toHaveCount(1);
});

it('clears every mode when the filter is "all"', function () {
    $store = app(HistoryStore::class);
    $store->push(GameModeType::CLASSIC, ['winner' => 'Alice', 'participants' => ['Alice', 'Bob']]);
    $store->push(GameModeType::COIN_FLIP, ['side' => 'pile']);

    Livewire::test(HistoryPage::class)->call('clear');

    expect($store->all(GameModeType::CLASSIC))->toBe([])
        ->and($store->all(GameModeType::COIN_FLIP))->toBe([]);
});

it('only lists filters for modes that are actually available', function () {
    $component = Livewire::test(HistoryPage::class);

    $values = array_column($component->instance()->availableFilters(), 'value');

    expect($values)->toContain(GameModeType::CLASSIC->value);
});

it('never proposes the "dev" category as a filter, since it has no available modes', function () {
    $component = Livewire::test(HistoryPage::class);

    $values = array_column($component->instance()->availableCategoryFilters(), 'value');

    expect($values)->not->toContain('dev');
});
