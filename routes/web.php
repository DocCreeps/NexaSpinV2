<?php

use App\Http\Controllers\HomeController;
use App\Livewire\CoinFlip\CoinFlipPage;
use App\Livewire\Dice\Dice421Page;
use App\Livewire\Draw\EliminationWheelPage;
use App\Livewire\Draw\WeightedWheelPage;
use App\Livewire\Draw\WheelPage;
use App\Livewire\History\HistoryPage;
use App\Livewire\Roulette\NumberRoulettePage;
use App\Livewire\Teams\TeamsPage;
use App\Livewire\Tournament\Bracket\DoubleEliminationBracketPage;
use App\Livewire\Tournament\Pool\PoolStagePage;
use App\Livewire\Tombola\TombolaPage;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::middleware('throttle:120,1')->group(function (): void {
    Route::get('/roue', WheelPage::class)->name('draw.wheel');
    Route::get('/roue-elimination', EliminationWheelPage::class)->name('draw.wheel-elimination');
    Route::get('/roue-ponderee', WeightedWheelPage::class)->name('draw.wheel-weighted');
    Route::get('/pile-ou-face', CoinFlipPage::class)->name('coinflip');
    Route::get('/421', Dice421Page::class)->name('dice.dice-421');
    Route::get('/equipes', TeamsPage::class)->name('teams');
    Route::get('/tombola', TombolaPage::class)->name('tombola');
    Route::get('/roulette', NumberRoulettePage::class)->name('roulette.number');
    Route::get('/historique', HistoryPage::class)->name('history');
    Route::get('/bracket', DoubleEliminationBracketPage::class)->name('draw.bracket');
    Route::get('/poules', PoolStagePage::class)->name('draw.pools');
});


