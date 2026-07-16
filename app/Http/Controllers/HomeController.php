<?php

namespace App\Http\Controllers;

use App\Application\Draw\Enums\DrawModeType;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        return view('home', [
            'modes' => DrawModeType::all(),
        ]);
    }
}
