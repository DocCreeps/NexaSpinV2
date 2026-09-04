<?php

use App\Application\Roulette\BankrollStore;

it('starts a new visitor off with the starting bankroll', function () {
    $store = new BankrollStore();

    expect($store->get())->toBe($store->startingAmount())
        ->and($store->startingAmount())->toBeGreaterThan(0);
});

it('remembers a bankroll amount that was set', function () {
    $store = new BankrollStore();

    $store->set(1500);

    expect($store->get())->toBe(1500);
});

it('never stores a negative bankroll, flooring it at zero', function () {
    $store = new BankrollStore();

    $store->set(-50);

    expect($store->get())->toBe(0);
});

it('restores the starting bankroll on reset', function () {
    $store = new BankrollStore();

    $store->set(0);
    $store->reset();

    expect($store->get())->toBe($store->startingAmount());
});
