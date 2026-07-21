<?php

namespace App\Providers;

use App\Domain\CoinFlip\Contracts\CoinFlipStrategy;
use App\Domain\CoinFlip\Strategies\RandomCoinFlipStrategy;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Lie le contrat du Domaine CoinFlip à son implémentation par défaut,
        // afin que FlipCoinAction reste découplée de toute stratégie concrète.
        $this->app->bind(CoinFlipStrategy::class, RandomCoinFlipStrategy::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
