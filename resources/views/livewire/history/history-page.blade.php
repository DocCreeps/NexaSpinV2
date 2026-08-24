<div class="min-h-screen w-full bg-surface text-ink antialiased selection:bg-secondary selection:text-ink" x-data="{ openEntry: null }" x-on:keydown.escape.window="openEntry = null">

    <div class="mx-auto max-w-4xl space-y-6 px-4 py-6 sm:space-y-8 sm:px-10 sm:py-10">

        {{-- HEADER --}}
        <header class="flex flex-col gap-4 border-b-4 border-ink pb-5 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 rounded-lg text-sm font-semibold text-muted transition-colors hover:text-ink focus:outline-none focus-visible:ring-2 focus-visible:ring-info focus-visible:ring-offset-2">
                    ← Salle
                </a>

                <p class="mt-3 font-mono text-[10px] uppercase tracking-widest text-faint">
                    ◆ Tous les tirages ◆
                </p>

                <h1 class="mt-1 font-display text-4xl leading-none text-ink sm:text-5xl">
                    Historique
                </h1>

                <p class="mt-2.5 max-w-md text-sm text-muted">
                    Retrouvez tous vos tirages, tous modes confondus, ou filtrez par mode de jeu.<br>
                    <span class="font-mono text-xs text-subtle">Conservation : 1 mois d'historique.</span>
                </p>
            </div>

            <div class="card-hard min-w-[110px] self-start rounded-xl border-2 border-ink bg-panel px-5 py-3 text-center shadow-sm">
                <p class="font-mono text-[9px] uppercase tracking-widest text-subtle">Tirages</p>
                <p class="mt-0.5 font-display text-2xl text-ink">
                    {{ count($this->entries) }}
                </p>
            </div>
        </header>

        {{-- FILTRES & BARRE D'ACTIONS --}}
        <div class="space-y-2.5" x-data="{ open: false }">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <button type="button" x-on:click="open = ! open" :aria-expanded="open" aria-controls="filters-panel" class="btn-press inline-flex items-center gap-2 rounded-xl border-2 border-ink bg-panel px-3.5 py-2 font-mono text-[11px] uppercase tracking-widest text-ink transition hover:bg-wash focus:outline-none focus-visible:ring-2 focus-visible:ring-info">
                    <span>Filtres</span>
                    @if($filter !== 'all')
                    <span class="rounded-full border border-ink bg-secondary px-2 py-0.5 font-mono text-[9px] font-bold text-ink">
                        {{ $filterType === 'category' ? \App\Application\Home\Enums\GameModeCategory::from($filter)->label() : \App\Application\Home\Enums\GameModeType::from($filter)->toDto()->title }}
                    </span>
                    @endif
                    <span class="text-[9px] transition-transform duration-200" :class="open ? 'rotate-180' : ''">▾</span>
                </button>

                @if(count($this->entries))
                <button type="button" wire:click="clear" wire:confirm="Vider {{ $filter === 'all' ? 'tout l’historique' : 'l’historique de ce filtre' }} ?" class="btn-press rounded-xl border-2 border-ink bg-panel px-3.5 py-2 font-mono text-[11px] uppercase tracking-widest text-subtle transition hover:border-danger hover:text-danger focus:outline-none focus-visible:ring-2 focus-visible:ring-danger">
                    Vider {{ $filter === 'all' ? 'tout' : 'ce filtre' }}
                </button>
                @endif
            </div>

            {{-- PANNEAU DE FILTRES EXPANDABLE --}}
            <div id="filters-panel" x-show="open" x-cloak x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2" class="card-hard space-y-3 rounded-2xl border-2 border-ink bg-panel p-4 shadow-md">

                {{-- Bascule type de filtre --}}
                <div class="inline-flex rounded-xl border-2 border-ink bg-wash p-1">
                    <button type="button" wire:click="setFilterType('mode')" @class([ 'rounded-lg px-3 py-1.5 font-mono text-[10px] uppercase tracking-widest font-bold transition' , 'bg-ink text-white shadow-sm'=> $filterType === 'mode',
                        'text-muted hover:text-ink' => $filterType !== 'mode',
                        ])>
                        Par mode
                    </button>
                    <button type="button" wire:click="setFilterType('category')" @class([ 'rounded-lg px-3 py-1.5 font-mono text-[10px] uppercase tracking-widest font-bold transition' , 'bg-ink text-white shadow-sm'=> $filterType === 'category',
                        'text-muted hover:text-ink' => $filterType !== 'category',
                        ])>
                        Par catégorie
                    </button>
                </div>

                {{-- Options du filtre actif --}}
                <div class="flex flex-wrap items-center gap-2 pt-1">
                    <button type="button" wire:click="setFilter('all')" x-on:click="open = false" @class([ 'btn-press rounded-xl border-2 border-ink px-3.5 py-1.5 font-mono text-[11px] uppercase tracking-widest transition' , 'bg-ink text-white shadow-hard'=> $filter === 'all',
                        'bg-wash text-muted hover:bg-panel hover:text-ink' => $filter !== 'all',
                        ])>
                        Tout
                    </button>

                    @php $options = $filterType === 'category' ? $this->availableCategoryFilters : $this->availableFilters; @endphp
                    @foreach($options as $option)
                    <button type="button" wire:click="setFilter('{{ $option['value'] }}')" x-on:click="open = false" @class([ 'btn-press rounded-xl border-2 border-ink px-3.5 py-1.5 font-mono text-[11px] uppercase tracking-widest transition' , 'bg-ink text-white shadow-hard'=> $filter === $option['value'],
                        'bg-wash text-muted hover:bg-panel hover:text-ink' => $filter !== $option['value'],
                        ])>
                        {{ $option['label'] }}
                    </button>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- LISTE DES ENTRÉES --}}
        @if(count($this->entries))
        <div class="space-y-3">
            @foreach($this->entries as $entry)
            @php
            $modeType = \App\Application\Home\Enums\GameModeType::tryFrom($entry['mode']);
            $modeTitle = $modeType ? $modeType->toDto()->title : $entry['mode'];
            $isWheelMode = in_array($entry['mode'], ['classic', 'weighted', 'elimination'], true);
            @endphp

            <div class="card-hard rounded-2xl border-2 border-ink bg-panel p-4 transition-all hover:border-ink hover:shadow-md sm:p-5">

                {{-- En-tête de la carte --}}
                <div class="flex flex-wrap items-center justify-between gap-2 border-b border-line/60 pb-2.5">
                    <span class="inline-flex items-center rounded-lg border-2 border-ink bg-wash px-2.5 py-0.5 font-mono text-[10px] font-bold uppercase tracking-wider text-ink">
                        {{ $modeTitle }}
                    </span>
                    <time datetime="{{ $entry['recorded_at'] }}" class="font-mono text-[11px] font-semibold text-faint">
                        {{ \Illuminate\Support\Carbon::parse($entry['recorded_at'])->format('d/m/Y H:i') }}
                    </time>
                </div>

                {{-- Contenu du tirage --}}
                <div class="mt-3 flex flex-wrap items-center justify-between gap-3">
                    <div class="min-w-0 flex-1 text-sm text-ink">
                        @switch($entry['mode'])
                        @case('coin_flip')
                        @php $type = $entry['type'] ?? 'single'; @endphp

                        @if($type === 'single')
                        {{-- Pile ou Face: unique --}}
                        <div class="flex flex-wrap items-center gap-2">
                            <span @class([ 'h-3 w-3 shrink-0 rounded-full border border-ink' , 'bg-secondary'=> ($entry['side'] ?? '') === 'face',
                                'bg-ink/40' => ($entry['side'] ?? '') === 'pile',
                                ])></span>

                            <span class="font-display text-base font-bold tracking-wide">
                                {{ $entry['side_label'] ?? ($entry['side'] === 'pile' ? 'Pile' : 'Face') }}
                            </span>

                            @if(isset($entry['bet']) && $entry['bet'] !== null)
                            <span class="text-xs text-muted">
                                · pari « <strong class="text-ink">{{ $entry['bet_label'] ?? ($entry['bet'] === 'pile' ? 'Pile' : 'Face') }}</strong> »
                            </span>
                            <span @class([ 'rounded-md border px-2 py-0.5 font-mono text-[10px] font-bold uppercase' , 'border-ink bg-secondary text-ink'=> $entry['bet_won'],
                                'border-danger/40 bg-danger/10 text-danger' => ! $entry['bet_won'],
                                ])>
                                {{ $entry['bet_won'] ? '✓ Gagné' : '✗ Perdu' }}
                            </span>
                            @endif
                        </div>
                        @else
                        {{-- Pile ou Face: série --}}
                        <div class="space-y-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="rounded-md border border-ink bg-wash px-2 py-0.5 font-mono text-[10px] font-bold uppercase text-ink">
                                    Série de {{ $entry['count'] }} tirages
                                </span>
                                <span class="font-display text-base font-bold tracking-wide">
                                    {{ $entry['winner_label'] }}
                                </span>
                            </div>

                            <div class="flex items-center gap-3 font-mono text-xs text-muted">
                                <span>{{ $entry['pile_label'] }}: <strong class="text-ink">{{ $entry['pile_count'] }}</strong></span>
                                <span>·</span>
                                <span>{{ $entry['face_label'] }}: <strong class="text-ink">{{ $entry['face_count'] }}</strong></span>
                            </div>
                        </div>
                        @endif
                        @break

                        @case('dice_421')
                        {{-- 421 --}}
                        <div class="flex flex-wrap items-center gap-2.5">
                            <span @class([ 'rounded-md border px-2 py-0.5 font-mono text-xs font-bold uppercase' , 'border-ink bg-secondary text-ink'=> $entry['won'],
                                'border-line bg-wash text-subtle' => ! $entry['won'],
                                ])>
                                {{ $entry['won'] ? '✓ Gagné' : '✗ Perdu' }}
                            </span>

                            @if($entry['combination'])
                            <span class="font-display text-base font-bold tracking-wide">{{ $entry['combination'] }}</span>
                            @endif

                            <span class="rounded border border-ink/20 bg-wash px-2 py-0.5 font-mono text-xs font-bold text-ink">
                                {{ implode(' · ', $entry['dice']) }}
                            </span>

                            <span class="font-mono text-[11px] text-faint">
                                {{ $entry['throws'] }} lancer{{ $entry['throws'] > 1 ? 's' : '' }}
                            </span>
                        </div>
                        @break

                        @case('classic')
                        @case('weighted')
                        @case('elimination')
                        {{-- Roues / Tirages au sort --}}
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-lg leading-none" aria-hidden="true">🏆</span>
                            <span class="font-display text-base font-bold tracking-wide text-ink">{{ $entry['winner'] }}</span>
                            <span class="font-mono text-[11px] text-subtle">
                                ({{ count($entry['participants']) }} participant{{ count($entry['participants']) > 1 ? 's' : '' }}
                                @if($entry['mode'] === 'weighted')
                                · poids {{ $entry['weights'][$entry['winner']] ?? '?' }}
                                @endif)
                            </span>
                        </div>
                        @break

                        @default
                        {{-- Cas générique/fallback --}}
                        @if(isset($entry['winner']))
                        <div class="flex items-center gap-2">
                            <span class="text-lg">🏆</span>
                            <span class="font-display text-base font-bold">{{ $entry['winner'] }}</span>
                        </div>
                        @endif
                        @endswitch
                    </div>

                    @if($isWheelMode)
                    <button type="button" x-on:click="openEntry = Object.assign({}, @js($entry), { modeLabel: @js($modeTitle) })" class="btn-press shrink-0 rounded-xl border-2 border-ink bg-wash px-3 py-1.5 font-mono text-[10px] font-bold uppercase tracking-wider text-ink transition hover:bg-ink hover:text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-info">
                        Détails
                    </button>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @else
        {{-- ÉTAT VIDE --}}
        <div class="rounded-2xl border-2 border-dashed border-line py-12 text-center bg-panel/50">
            <p class="mb-1 text-3xl" aria-hidden="true">🗂️</p>
            <p class="text-sm font-semibold text-muted">Aucun tirage trouvé</p>
            <p class="mt-1 text-xs text-faint">
                {{ $filter !== 'all' ? 'Aucun résultat pour ce filtre.' : 'Lancez un tirage sur l’un des modes pour le voir apparaître ici.' }}
            </p>
        </div>
        @endif
    </div>

    {{-- MODALE DES DÉTAILS --}}
    <x-history.details-modal />
</div>
