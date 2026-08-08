<?php

namespace App\Livewire\History;

use App\Application\History\HistoryStore;
use App\Application\Home\Enums\GameModeType;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Page unique regroupant l'historique de tous les modes de jeu, filtrable par mode.
 */
class HistoryPage extends Component
{
    /** Filtre courant : 'all' ou la valeur d'un GameModeType. Reflété dans l'URL (?filter=). */
    #[Url]
    public string $filter = 'all';

    public function setFilter(string $filter): void
    {
        $this->filter = $filter;

        unset($this->entries);
    }

    /**
     * Vide l'historique du filtre courant (tous les modes si 'all').
     */
    public function clear(): void
    {
        $store = app(HistoryStore::class);
        $mode = GameModeType::tryFrom($this->filter);

        if ($mode !== null) {
            $store->clear($mode);
        } else {
            $store->clearAll();
        }

        unset($this->entries);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    #[Computed]
    public function entries(): array
    {
        $store = app(HistoryStore::class);
        $mode = GameModeType::tryFrom($this->filter);

        if ($mode !== null) {
            return array_map(
                static fn(array $entry) => $entry + ['mode' => $mode->value],
                $store->all($mode)
            );
        }

        return $store->allModes();
    }

    /**
     * Liste des modes filtrables (Teams exclu : non disponible / sans historique).
     *
     * @return array<int, array{value: string, label: string}>
     */
    #[Computed]
    public function availableFilters(): array
    {
        return collect(GameModeType::cases())
            ->reject(fn(GameModeType $mode) => $mode === GameModeType::TEAMS)
            ->map(fn(GameModeType $mode) => [
                'value' => $mode->value,
                'label' => $mode->toDto()->title,
            ])
            ->values()
            ->all();
    }

    public function render()
    {
        return view('livewire.history.history-page')
            ->layout('layouts.app', [
                'title' => 'Historique des tirages | NexaSpin',
                'metaDescription' => "Retrouvez l'historique de tous vos tirages : pile ou face, 421, roue classique, roue pondérée et roue par élimination.",
            ]);
    }
}
