<?php

use App\Http\Controllers\HomeController;
use App\Livewire\CoinFlip\CoinFlipPage;
use App\Livewire\Draw\EliminationWheelPage;
use App\Livewire\Draw\WeightedWheelPage;
use App\Livewire\Draw\WheelPage;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

// Le throttle limite les requêtes HTTP initiales (chargement de page) ET,
// plus important ici, les appels AJAX déclenchés par les actions Livewire
// (addParticipant, draw, flip...), qui transitent tous par la même route.
Route::middleware('throttle:120,1')->group(function (): void {
    Route::get('/roue', WheelPage::class)->name('draw.wheel');
    Route::get('/roue-elimination', EliminationWheelPage::class)->name('draw.wheel-elimination');
    Route::get('/roue-ponderee', WeightedWheelPage::class)->name('draw.wheel-weighted');
    Route::get('/pile-ou-face', CoinFlipPage::class)->name('draw.coinflip');
});


