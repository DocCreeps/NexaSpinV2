<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate';
    protected $description = 'Génère le sitemap.xml statique de NexaSpin à partir des routes GET publiques';

    /**
     * Priorité par route nommée. Tout ce qui n'est pas listé ici récupère 0.5.
     */
    private const PRIORITIES = [
        'home' => 1.0,
        'draw.wheel' => 0.8,
        'draw.wheel-elimination' => 0.8,
        'draw.wheel-weighted' => 0.8,
    ];

    public function handle(): void
    {
        $sitemap = Sitemap::create();

        foreach ($this->publicRoutes() as $route) {
            $sitemap->add(
                Url::create(route($route->getName()))
                    ->setPriority(self::PRIORITIES[$route->getName()] ?? 0.5)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
            );
        }

        $sitemap->writeToFile(public_path('sitemap.xml'));

        $this->info('sitemap.xml généré dans public/ (' . $this->publicRoutes()->count() . ' URLs).');
    }

    /**
     * @return \Illuminate\Support\Collection<int, \Illuminate\Routing\Route>
     */
    private function publicRoutes()
    {
        return collect(Route::getRoutes())
            ->filter(fn($route) => in_array('GET', $route->methods())
                && $route->getName() !== null
                && ! str_starts_with($route->getName(), 'livewire.')
                && empty($route->parameterNames()));
    }
}
