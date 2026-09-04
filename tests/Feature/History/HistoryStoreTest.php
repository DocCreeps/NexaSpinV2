<?php

use App\Application\History\HistoryStore;
use App\Application\Home\Enums\GameModeType;

afterEach(function () {
    Illuminate\Support\Carbon::setTestNow();
});

it('returns an empty history for a mode with no entries yet', function () {
    $store = new HistoryStore();

    expect($store->all(GameModeType::CLASSIC))->toBe([]);
});

it('remembers a pushed entry and puts the most recent one first', function () {
    $store = new HistoryStore();

    $store->push(GameModeType::CLASSIC, ['winner' => 'Alice']);
    $store->push(GameModeType::CLASSIC, ['winner' => 'Bob']);

    $entries = $store->all(GameModeType::CLASSIC);

    expect($entries)->toHaveCount(2)
        ->and($entries[0]['winner'])->toBe('Bob')
        ->and($entries[1]['winner'])->toBe('Alice');
});

it('stamps every pushed entry with a recorded_at timestamp', function () {
    $store = new HistoryStore();

    $store->push(GameModeType::CLASSIC, ['winner' => 'Alice']);

    expect($store->all(GameModeType::CLASSIC)[0])->toHaveKey('recorded_at');
});

it('keeps each game mode history separate', function () {
    $store = new HistoryStore();

    $store->push(GameModeType::CLASSIC, ['winner' => 'Alice']);
    $store->push(GameModeType::COIN_FLIP, ['side' => 'pile']);

    expect($store->all(GameModeType::CLASSIC))->toHaveCount(1)
        ->and($store->all(GameModeType::COIN_FLIP))->toHaveCount(1);
});

it('limits how many entries are returned when a limit is given', function () {
    $store = new HistoryStore();

    for ($i = 0; $i < 5; $i++) {
        $store->push(GameModeType::CLASSIC, ['winner' => "Player{$i}"]);
    }

    expect($store->all(GameModeType::CLASSIC, limit: 2))->toHaveCount(2)
        ->and($store->all(GameModeType::CLASSIC))->toHaveCount(5);
});

it('clears the history of a single mode without affecting the others', function () {
    $store = new HistoryStore();

    $store->push(GameModeType::CLASSIC, ['winner' => 'Alice']);
    $store->push(GameModeType::COIN_FLIP, ['side' => 'pile']);

    $store->clear(GameModeType::CLASSIC);

    expect($store->all(GameModeType::CLASSIC))->toBe([])
        ->and($store->all(GameModeType::COIN_FLIP))->toHaveCount(1);
});

it('clears every mode at once with clearAll', function () {
    $store = new HistoryStore();

    $store->push(GameModeType::CLASSIC, ['winner' => 'Alice']);
    $store->push(GameModeType::COIN_FLIP, ['side' => 'pile']);

    $store->clearAll();

    expect($store->all(GameModeType::CLASSIC))->toBe([])
        ->and($store->all(GameModeType::COIN_FLIP))->toBe([]);
});

it('merges every mode together, most recent entry first, tagged with its mode', function () {
    $store = new HistoryStore();

    Illuminate\Support\Carbon::setTestNow('2024-01-01 10:00:00');
    $store->push(GameModeType::CLASSIC, ['winner' => 'Alice']);

    Illuminate\Support\Carbon::setTestNow('2024-01-01 10:00:05');
    $store->push(GameModeType::COIN_FLIP, ['side' => 'pile']);

    // The cache TTL was computed from the frozen clock above, so it must still be
    // active when we read it back — resetting to the real clock first would make
    // these entries look expired (real "now" is long past the frozen 2024 date).
    // (afterEach() above restores the real clock once this test ends.)
    $merged = $store->allModes();

    expect($merged)->toHaveCount(2)
        ->and($merged[0]['mode'])->toBe(GameModeType::COIN_FLIP->value)
        ->and($merged[1]['mode'])->toBe(GameModeType::CLASSIC->value);
});
