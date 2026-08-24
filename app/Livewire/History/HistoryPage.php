<?php

namespace App\Livewire\History;

use App\Application\History\HistoryStore;
use App\Application\Home\Enums\GameModeCategory;
use App\Application\Home\Enums\GameModeType;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Page unique regroupant l'historique de tous les modes de jeu, filtrable par mode
 * ou par catégorie de mode.
 */
class HistoryPage extends Component
{
    /** Type de filtre courant : 'mode' ou 'category'. Reflété dans l'URL (?filterType=). */
    #[Url]
    public string $filterType = 'mode';

    /**
     * Filtre courant : 'all', ou la valeur d'un GameModeType (filterType 'mode')
     * ou d'une GameModeCategory (filterType 'category'). Reflété dans l'URL (?filter=).
     */
    #[Url]
    public string $filter = 'all';

    /**
     * Bascule le type de filtre (mode / catégorie) et réinitialise le filtre à 'all'.
     */
    public function setFilterType(string $filterType): void
    {
        if (! in_array($filterType, ['mode', 'category'], true)) {
            return;
        }

        $this->filterType = $filterType;
        $this->filter = 'all';

        unset($this->entries);
    }

    public function setFilter(string $filter): void
    {
        $this->filter = $filter;

        unset($this->entries);
    }

    /**
     * Vide l'historique du filtre courant (tous les modes si 'all', tous les modes
     * de la catégorie si un filtre par catégorie est actif).
     */
    public function clear(): void
    {
        $store = app(HistoryStore::class);

        $modesToClear = $this->filterType === 'category'
            ? $this->modesForCategory(GameModeCategory::tryFrom($this->filter))
            : array_filter([GameModeType::tryFrom($this->filter)]);

        if ($modesToClear !== []) {
            foreach ($modesToClear as $mode) {
                $store->clear($mode);
            }
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

        if ($this->filterType === 'category') {
            $category = GameModeCategory::tryFrom($this->filter);

            if ($category === null) {
                return $store->allModes();
            }

            $modes = $this->modesForCategory($category);

            return collect($store->allModes())
                ->filter(fn(array $entry) => in_array(GameModeType::from($entry['mode']), $modes, true))
                ->values()
                ->all();
        }

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

    /**
     * Liste des catégories filtrables (catégorie DEV exclue : aucun mode disponible,
     * donc jamais d'historique possible).
     *
     * @return array<int, array{value: string, label: string}>
     */
    #[Computed]
    public function availableCategoryFilters(): array
    {
        return collect(GameModeType::grouped())
            ->reject(fn(array $group) => $group['category'] === GameModeCategory::DEV)
            ->map(fn(array $group) => [
                'value' => $group['category']->value,
                'label' => $group['category']->label(),
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<int, GameModeType>
     */
    private function modesForCategory(?GameModeCategory $category): array
    {
        if ($category === null) {
            return [];
        }

        return collect(GameModeType::cases())
            ->filter(fn(GameModeType $mode) => $mode->toDto()->category === $category)
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
