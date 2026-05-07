<?php

use Illuminate\Support\Facades\Route;

use App\Livewire\Draw\RandomDraw;

Route::get('/', RandomDraw::class);
