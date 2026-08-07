<?php

namespace App\Application\History;

use App\Application\Home\Enums\GameModeType;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Carbon;

/**
 * Stocke et relit l'historique des tirages d'un visiteur, en cache, par mode de jeu.
 *
 * Rattachement : l'ID de session Laravel (voir config/session.php, durée de vie
 * étendue à 3 mois) sert d'identifiant. Pas de compte, pas de BDD : le cache est
 * la seule source de vérité, avec un TTL propre qui rejoint la durée de session.
 *
 * Chaque entrée est un tableau libre (forme différente par mode), auquel on ajoute
 * automatiquement une clé `recorded_at` (ISO 8601) au moment de l'écriture.
 */
class HistoryStore
{
    /** Nombre maximum d'entrées conservées par mode et par visiteur. */
    private const MAX_ENTRIES = 200;

    /** Durée de rétention du cache, alignée sur la durée de vie de la session (3 mois). */
    private const TTL_DAYS = 90;

    /**
     * Enregistre une nouvelle entrée d'historique pour un mode donné (la plus
     * récente en tête).
     *
     * @param  array<string, mixed>  $entry
     */
    public function push(GameModeType $mode, array $entry): void
    {
        $entries = $this->all($mode);

        $entry['recorded_at'] = Carbon::now()->toIso8601String();

        array_unshift($entries, $entry);

        if (count($entries) > self::MAX_ENTRIES) {
            $entries = array_slice($entries, 0, self::MAX_ENTRIES);
        }

        Cache::put($this->key($mode), $entries, Carbon::now()->addDays(self::TTL_DAYS));
    }

    /**
     * Retourne l'historique d'un mode, du plus récent au plus ancien.
     *
     * @return array<int, array<string, mixed>>
     */
    public function all(GameModeType $mode, ?int $limit = null): array
    {
        $entries = Cache::get($this->key($mode), []);

        return $limit !== null ? array_slice($entries, 0, $limit) : $entries;
    }

    /**
     * Retourne l'historique de tous les modes fusionné et trié par date décroissante,
     * chaque entrée étant enrichie d'une clé `mode` (valeur de GameModeType).
     *
     * @return array<int, array<string, mixed>>
     */
    public function allModes(): array
    {
        $merged = [];

        foreach (GameModeType::cases() as $mode) {
            foreach ($this->all($mode) as $entry) {
                $entry['mode'] = $mode->value;
                $merged[] = $entry;
            }
        }

        usort(
            $merged,
            fn(array $a, array $b) => $b['recorded_at'] <=> $a['recorded_at']
        );

        return $merged;
    }

    /**
     * Vide l'historique d'un mode pour le visiteur courant.
     */
    public function clear(GameModeType $mode): void
    {
        Cache::forget($this->key($mode));
    }

    /**
     * Vide l'historique de tous les modes pour le visiteur courant.
     */
    public function clearAll(): void
    {
        foreach (GameModeType::cases() as $mode) {
            $this->clear($mode);
        }
    }

    private function key(GameModeType $mode): string
    {
        return sprintf('history:%s:%s', session()->getId(), $mode->value);
    }
}
