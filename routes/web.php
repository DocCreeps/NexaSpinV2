<?php

use App\Http\Controllers\HomeController;
use App\Livewire\CoinFlip\CoinFlipPage;
use App\Livewire\Dice\Dice421Page;
use App\Livewire\Draw\EliminationWheelPage;
use App\Livewire\Draw\WeightedWheelPage;
use App\Livewire\Draw\WheelPage;
use App\Livewire\History\HistoryPage;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::middleware('throttle:120,1')->group(function (): void {
    Route::get('/roue', WheelPage::class)->name('draw.wheel');
    Route::get('/roue-elimination', EliminationWheelPage::class)->name('draw.wheel-elimination');
    Route::get('/roue-ponderee', WeightedWheelPage::class)->name('draw.wheel-weighted');
    Route::get('/pile-ou-face', CoinFlipPage::class)->name('coinflip');
    Route::get('/421', Dice421Page::class)->name('dice.dice-421');
    Route::get('/historique', HistoryPage::class)->name('history');
});


