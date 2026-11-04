<?php

namespace App\Application\SecretNumber;

use App\Domain\SecretNumber\Entities\SecretNumberGame;
use App\Domain\SecretNumber\Exceptions\InvalidSecretNumberGameException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

/** Source de vérité serveur des parties, notamment du nombre secret en cours. */
final class SecretNumberGameStore
{
    /** Une partie n'est pas reprenable après rechargement de la page : on ne la garde que le temps de jouer. */
    private const TTL_HOURS = 6;

    public function save(string $roundId, SecretNumberGame $game): void
    {
        Cache::put($this->key($roundId), $game->toArray(), Carbon::now()->addHours(self::TTL_HOURS));
    }

    /** Retourne null si la partie a expiré ou si son format n'est plus lisible (après un déploiement par exemple). */
    public function load(string $roundId): ?SecretNumberGame
    {
        $state = Cache::get($this->key($roundId));
        if (! is_array($state)) {
            return null;
        }

        try {
            return SecretNumberGame::fromArray($state);
        } catch (InvalidSecretNumberGameException) {
            $this->clear($roundId);

            return null;
        }
    }

    public function clear(string $roundId): void
    {
        Cache::forget($this->key($roundId));
    }

    private function key(string $roundId): string
    {
        return sprintf('secret-number:game:%s:%s', session()->getId(), $roundId);
    }
}
