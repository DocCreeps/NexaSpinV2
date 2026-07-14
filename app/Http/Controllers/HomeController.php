<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class HomeController extends Controller
{
public function __invoke(): View
{
$modes = [
[
'icon' => '🎡',
'title' => 'Roue classique',
'description' => 'Un seul tour suffit pour désigner instantanément un gagnant parmi tous les participants.',
'route' => route('draw.wheel'),
'available' => true,
'color' => 'from-indigo-500 to-purple-600',
'shadow' => 'shadow-indigo-500/10 hover:shadow-indigo-500/20',
],
[
'icon' => '⚔️',
'title' => 'Roue par élimination',
'description' => 'Les participants s’affrontent tour après tour jusqu’au dernier survivant.',
'route' => route('draw.wheel-elimination'),
'available' => true,
'color' => 'from-red-500 to-orange-500',
'shadow' => 'shadow-red-500/10 hover:shadow-red-500/20',
],
[
'icon' => '🎯',
'title' => 'Tirage pondéré',
'description' => 'Attribuez des probabilités différentes selon un système de poids personnalisé.',
'route' => null,
'available' => false,
'color' => 'from-slate-400 to-slate-500',
'shadow' => 'shadow-gray-500/5',
],
];

return view('home', compact('modes'));
}
}
