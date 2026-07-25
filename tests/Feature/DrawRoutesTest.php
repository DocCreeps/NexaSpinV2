<?php

use App\Livewire\CoinFlip\CoinFlipPage;
use App\Livewire\Draw\EliminationWheelPage;
use App\Livewire\Draw\WheelPage;

test('the wheel page loads the wheel component', function () {
    $response = $this->get(route('draw.wheel'));

    $response->assertStatus(200)
        ->assertSeeLivewire(WheelPage::class);
});

test('the elimination wheel page loads the elimination component', function () {
    $response = $this->get(route('draw.wheel-elimination'));

    $response->assertStatus(200)
        ->assertSeeLivewire(EliminationWheelPage::class);
});

test('the coinflip page loads the CoinFlipPage component', function () {
    $response = $this->get(route('draw.coinflip'));

    $response->assertStatus(200)
        ->assertSeeLivewire(CoinFlipPage::class);
});
