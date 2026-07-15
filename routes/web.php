<?php

use App\Http\Controllers\HomeController;
use App\Livewire\Draw\EliminationWheelPage;
use App\Livewire\Draw\WheelPage;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::get('/roue', WheelPage::class)->name('draw.wheel');
Route::get('/roue-elimination', EliminationWheelPage::class)->name('draw.wheel-elimination');
