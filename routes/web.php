<?php

use Illuminate\Support\Facades\Route;

use App\Livewire\Draw\WheelPage;
use App\Livewire\Draw\EliminationWheelPage;

Route::view('/', 'home')->name('home');

Route::get('/roue', WheelPage::class)->name('draw.wheel');
Route::get('/roue-elimination', EliminationWheelPage::class)->name('draw.wheel-elimination');
