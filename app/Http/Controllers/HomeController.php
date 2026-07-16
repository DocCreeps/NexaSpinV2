<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

/**
 * Petit Read-Model immuable dédié à la présentation sur la Home.
 */
final readonly class DrawMode
{
    public function __construct(
        public string $icon,
        public string $title,
        public string $description,
        public ?string $route,
        public bool $available,
        public string $color,
        public string $shadow,
    ) {}
}

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $modes = [
            new DrawMode(
                icon: '🎡',
                title: 'Roue classique',
                description: 'Un seul tour suffit pour désigner instantanément un gagnant parmi tous les participants.',
                route: route('draw.wheel'),
                available: true,
                color: 'from-indigo-500 to-purple-600',
                shadow: 'shadow-indigo-500/10 hover:shadow-indigo-500/20',
            ),
            new DrawMode(
                icon: '⚔️',
                title: 'Roue par élimination',
                description: 'Les participants s’affrontent tour après tour jusqu’au dernier survivant.',
                route: route('draw.wheel-elimination'),
                available: true,
                color: 'from-red-500 to-orange-500',
                shadow: 'shadow-red-500/10 hover:shadow-red-500/20',
            ),
            new DrawMode(
                icon: '🎯',
                title: 'Tirage pondéré',
                description: 'Attribuez des probabilités différentes selon un système de poids personnalisé.',
                route: route('draw.wheel-weighted'),
                available: true,
                color: 'from-orange-500 to-amber-500',
                shadow: 'shadow-orange-500/10 hover:shadow-orange-500/20',
            ),
        ];

        return view('home', compact('modes'));
    }
}
