<?php

namespace App\Application\Roulette;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * Persiste la cagnotte de la roulette numérique d'un visiteur, en cache.
 *
 * Même identifiant (session Laravel) et même durée de rétention (1 mois) que
 * l'historique des tirages (voir App\Application\History\HistoryStore) : pas
 * de compte, pas de BDD, le cache est la seule source de vérité.
 */
class BankrollStore
{
    private const TTL_DAYS = 30;

    private const STARTING_BANKROLL = 1000;

    public function get(): int
    {
        return Cache::get($this->key(), self::STARTING_BANKROLL);
    }

    public function set(int $amount): void
    {
        Cache::put($this->key(), max($amount, 0), Carbon::now()->addDays(self::TTL_DAYS));
    }

    public function reset(): void
    {
        Cache::put($this->key(), self::STARTING_BANKROLL, Carbon::now()->addDays(self::TTL_DAYS));
    }

    public function startingAmount(): int
    {
        return self::STARTING_BANKROLL;
    }

    private function key(): string
    {
        return sprintf('roulette:bankroll:%s', session()->getId());
    }
}
