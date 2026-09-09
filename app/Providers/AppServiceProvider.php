<?php

namespace App\Providers;

use App\Domain\CoinFlip\Contracts\CoinFlipStrategy;
use App\Domain\CoinFlip\Strategies\RandomCoinFlipStrategy;
use App\Domain\RockPaperScissors\Contracts\RpsOpponentStrategy;
use App\Domain\RockPaperScissors\Strategies\RandomRpsStrategy;
use App\Domain\TicTacToe\Contracts\TicTacToeOpponentStrategy;
use App\Domain\TicTacToe\Strategies\HeuristicTicTacToeStrategy;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(CoinFlipStrategy::class, RandomCoinFlipStrategy::class);
        $this->app->bind(RpsOpponentStrategy::class, RandomRpsStrategy::class); // ajout
        $this->app->bind(TicTacToeOpponentStrategy::class, HeuristicTicTacToeStrategy::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
