<?php

it('loads the home page successfully', function () {
    $this->get(route('home'))->assertOk();
});

it('loads every game mode page successfully', function (string $routeName) {
    $this->get(route($routeName))->assertOk();
})->with([
    'draw.wheel',
    'draw.wheel-elimination',
    'draw.wheel-weighted',
    'coinflip',
    'dice.dice-421',
    'teams',
    'tombola',
    'roulette.number',
    'history',
    'draw.bracket',
    'draw.pools',
]);
