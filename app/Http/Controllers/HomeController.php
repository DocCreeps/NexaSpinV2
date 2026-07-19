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
            'title' => 'NexaSpin — Tirages au Sort & Roues de la Décision Aléatoires',
            'metaDescription' => 'Créez des tirages au sort gratuits et instantanés. Utilisez notre roue de la décision classique, par élimination ou tirage pondéré pour animer vos jeux et concours.',
        ]);
    }
}
