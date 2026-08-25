<?php

namespace App\Application\Tournament;

use App\Application\Home\Enums\GameModeType;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Carbon;

/**
 * Sauvegarde l'état d'un tournoi EN COURS (bracket, double élimination, poules...)
 * pour le visiteur courant, le temps qu'il ne soit pas terminé.
 *
 * Différence avec HistoryStore : celui-ci archive une LISTE d'entrées pour des
 * tournois déjà terminés (le plus récent en tête) ; celui-ci ne garde qu'UNE
 * seule entrée par mode, écrasée à chaque sauvegarde, et effacée dès que le
 * tournoi se termine ou est explicitement réinitialisé. Même mécanisme de
 * rattachement (ID de session Laravel, cache avec TTL aligné sur la durée de
 * session) pour rester cohérent avec le reste de l'application : pas de compte,
 * pas de BDD.
 */
class TournamentProgressStore
{
    /** Durée de rétention du cache, alignée sur la durée de vie de la session (1 mois). */
    private const TTL_DAYS = 30;

    /**
     * @param  array<string, mixed>  $state
     */
    public function save(GameModeType $mode, array $state): void
    {
        Cache::put($this->key($mode), $state, Carbon::now()->addDays(self::TTL_DAYS));
    }

    /**
     * @return array<string, mixed>|null Null si aucun tournoi en cours pour ce mode.
     */
    public function load(GameModeType $mode): ?array
    {
        return Cache::get($this->key($mode));
    }

    public function has(GameModeType $mode): bool
    {
        return Cache::has($this->key($mode));
    }

    public function clear(GameModeType $mode): void
    {
        Cache::forget($this->key($mode));
    }

    private function key(GameModeType $mode): string
    {
        return sprintf('tournament-progress:%s:%s', session()->getId(), $mode->value);
    }
}
