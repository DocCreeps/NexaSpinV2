<div class="min-h-screen w-full bg-surface text-ink antialiased selection:bg-secondary selection:text-ink" x-data="{ openEntry: null }" x-on:keydown.escape.window="openEntry = null">
    <div class="mx-auto max-w-4xl space-y-6 px-4 py-6 sm:space-y-8 sm:px-10 sm:py-10">

        {{-- HEADER --}}
        <header class="flex flex-col gap-4 border-b-4 border-ink pb-5 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 rounded text-sm font-semibold text-muted transition-colors hover:text-ink focus:outline-none focus-visible:ring-2 focus-visible:ring-info focus-visible:ring-offset-2">
                    ← Salle
                </a>

                <p class="mt-4 font-mono text-[10px] uppercase tracking-widest text-faint">
                    ◆ Tous les tirages ◆
                </p>

                <h1 class="mt-1 font-display text-4xl leading-none text-ink sm:text-5xl">
                    Historique
                </h1>

                <p class="mt-3 max-w-md text-sm text-muted">
                    Retrouvez tous vos tirages, tous modes confondus, ou filtrez par mode de jeu.<br> 1 mois d'historique.
                </p>
            </div>

            <div class="card-hard self-start rounded-xl border-2 border-ink bg-panel px-5 py-3 text-center min-w-[110px]">
                <p class="font-mono text-[9px] uppercase tracking-widest text-subtle">Tirages</p>
                <p class="mt-0.5 font-display text-2xl text-ink">
                    {{ count($this->entries) }}
                </p>
            </div>
        </header>

        {{-- FILTRES --}}
        <div class="space-y-2.5" x-data="{ open: false }">
            <div class="flex flex-wrap items-center gap-2">
                <button type="button" x-on:click="open = ! open" class="inline-flex items-center gap-2 rounded-xl border-2 border-ink bg-panel px-3.5 py-2 font-mono text-[11px] uppercase tracking-widest text-ink transition hover:bg-wash">
                    <span>Filtres</span>
                    @if($filter !== 'all')
                    <span class="rounded-full border border-ink bg-secondary px-1.5 font-mono text-[9px] font-bold text-ink">
                        {{ $filterType === 'category' ? \App\Application\Home\Enums\GameModeCategory::from($filter)->label() : \App\Application\Home\Enums\GameModeType::from($filter)->toDto()->title }}
                    </span>
                    @endif
                    <span class="text-[9px] transition-transform" x-bind:class="open ? 'rotate-180' : ''">▾</span>
                </button>

                @if(count($this->entries))
                <button type="button" wire:click="clear" wire:confirm="Vider {{ $filter === 'all' ? 'tout l’historique' : 'l’historique de ce filtre' }} ?" class="ml-auto rounded-xl border-2 border-ink bg-panel px-3.5 py-2 font-mono text-[11px] uppercase tracking-widest text-subtle transition hover:text-danger">
                    Vider {{ $filter === 'all' ? 'tout' : 'ce filtre' }}
                </button>
                @endif
            </div>

            <div x-show="open" x-cloak x-transition.opacity.duration.150ms class="card-hard space-y-3 rounded-xl border-2 border-ink bg-panel p-3.5">
                {{-- Bascule type de filtre --}}
                <div class="inline-flex rounded-lg border-2 border-ink bg-wash p-0.5">
                    <button type="button" wire:click="setFilterType('mode')" @class([
                        'rounded-md px-3 py-1.5 font-mono text-[10px] uppercase tracking-widest transition',
                        'bg-ink text-white' => $filterType === 'mode',
                        'text-muted' => $filterType !== 'mode',
                        ])>
                        Par mode
                    </button>
                    <button type="button" wire:click="setFilterType('category')" @class([
                        'rounded-md px-3 py-1.5 font-mono text-[10px] uppercase tracking-widest transition',
                        'bg-ink text-white' => $filterType === 'category',
                        'text-muted' => $filterType !== 'category',
                        ])>
                        Par catégorie
                    </button>
                </div>

                {{-- Options du filtre actif --}}
                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" wire:click="setFilter('all')" @class([
                        'rounded-xl border-2 border-ink px-3.5 py-2 font-mono text-[11px] uppercase tracking-widest transition',
                        'bg-ink text-white shadow-hard' => $filter === 'all',
                        'bg-wash text-muted' => $filter !== 'all',
                        ])>
                        Tout
                    </button>

                    @php $options = $filterType === 'category' ? $this->availableCategoryFilters : $this->availableFilters; @endphp
                    @foreach($options as $option)
                    <button type="button" wire:click="setFilter('{{ $option['value'] }}')" @class([
                        'rounded-xl border-2 border-ink px-3.5 py-2 font-mono text-[11px] uppercase tracking-widest transition',
                        'bg-ink text-white shadow-hard' => $filter === $option['value'],
                        'bg-wash text-muted' => $filter !== $option['value'],
                        ])>
                        {{ $option['label'] }}
                    </button>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- LISTE --}}
        @if(count($this->entries))
        <div class="custom-scrollbar space-y-2.5">
            @foreach($this->entries as $entry)
            @php $isWheelMode = in_array($entry['mode'], ['classic', 'weighted', 'elimination'], true); @endphp
            <div class="card-hard rounded-2xl border-2 border-ink bg-panel px-4 py-3.5 sm:px-5">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <span class="rounded-md border-2 border-ink bg-wash px-2.5 py-0.5 font-mono text-[10px] uppercase tracking-widest text-subtle">
                        {{ \App\Application\Home\Enums\GameModeType::from($entry['mode'])->toDto()->title }}
                    </span>
                    <span class="font-mono text-[11px] text-faint">
                        {{ \Illuminate\Support\Carbon::parse($entry['recorded_at'])->format('d/m/Y H:i') }}
                    </span>
                </div>

                <div class="mt-2.5 flex flex-wrap items-center justify-between gap-3">
                    <div class="min-w-0 flex-1 text-sm text-ink">
                        @switch($entry['mode'])
                        @case('coin_flip')
                        @php $type = $entry['type'] ?? 'single'; @endphp

                        @if($type === 'single')
                        {{-- TIRAGE UNIQUE --}}
                        <div class="flex flex-wrap items-center gap-2">
                            <span @class([
                                'h-2.5 w-2.5 shrink-0 rounded-full',
                                'bg-secondary' => ($entry['side'] ?? '') === 'face',
                                'bg-ink/40' => ($entry['side'] ?? '') === 'pile',
                                ])></span>
                            <span class="font-display text-base tracking-wide">
                                {{ $entry['side_label'] ?? ($entry['side'] === 'pile' ? 'Pile' : 'Face') }}
                            </span>

                            @if(isset($entry['bet']) && $entry['bet'] !== null)
                            <span class="text-xs text-muted">
                                · pari « <strong class="text-ink">{{ $entry['bet_label'] ?? ($entry['bet'] === 'pile' ? 'Pile' : 'Face') }}</strong> »
                            </span>
                            <span @class([
                                'rounded-md border px-1.5 py-0.5 font-mono text-[10px] font-bold uppercase',
                                'border-ink bg-secondary text-ink' => $entry['bet_won'],
                                'border-danger/30 bg-danger/10 text-danger' => ! $entry['bet_won'],
                                ])>
                                {{ $entry['bet_won'] ? '✓ Gagné' : '✗ Perdu' }}
                            </span>
                            @endif
                        </div>

                        @else
                        {{-- TIRAGE MULTIPLE (SÉRIE AUTO) --}}
                        <div class="space-y-1.5">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="rounded-md border border-ink bg-wash px-2 py-0.5 font-mono text-[10px] uppercase font-bold text-ink">
                                    Série de {{ $entry['count'] }} tirages
                                </span>
                                <span class="font-display text-base tracking-wide">
                                    {{ $entry['winner_label'] }}
                                </span>
                            </div>

                            <div class="flex items-center gap-3 text-xs text-muted font-mono">
                                <span>{{ $entry['pile_label'] }}: <strong class="text-ink">{{ $entry['pile_count'] }}</strong></span>
                                <span>·</span>
                                <span>{{ $entry['face_label'] }}: <strong class="text-ink">{{ $entry['face_count'] }}</strong></span>
                            </div>
                        </div>
                        @endif
                        @break

                        @case('dice_421')
                        <div class="flex flex-wrap items-center gap-2.5">
                            <span @class([
                                'rounded-md border px-2 py-0.5 font-mono text-xs font-bold uppercase',
                                'border-ink bg-secondary text-ink' => $entry['won'],
                                'border-line bg-wash text-subtle' => ! $entry['won'],
                                ])>
                                {{ $entry['won'] ? '✓ Gagné' : '✗ Perdu' }}
                            </span>

                            @if($entry['combination'])
                            <span class="font-display text-base tracking-wide">{{ $entry['combination'] }}</span>
                            @endif

                            <span class="font-mono text-xs text-subtle">
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
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-lg leading-none">🏆</span>
                            <span class="font-display text-base tracking-wide">{{ $entry['winner'] }}</span>
                            <span class="font-mono text-[11px] text-subtle">
                                {{ count($entry['participants']) }} participant{{ count($entry['participants']) > 1 ? 's' : '' }}
                                @if($entry['mode'] === 'weighted')
                                · poids {{ $entry['weights'][$entry['winner']] ?? '?' }}
                                @endif
                            </span>
                        </div>
                        @break

                        @case('teams')
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-lg leading-none">👥</span>
                            <span class="font-display text-base tracking-wide">{{ $entry['teams_count'] }} équipes</span>
                            <span class="font-mono text-[11px] text-subtle">
                                {{ count($entry['participants']) }} participant{{ count($entry['participants']) > 1 ? 's' : '' }}
                                @if(count($entry['substitutes'] ?? []))
                                · {{ count($entry['substitutes']) }} remplaçant{{ count($entry['substitutes']) > 1 ? 's' : '' }}
                                @endif
                            </span>
                        </div>
                        @break

                        @case('tombola')
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-lg leading-none">🎟️</span>
                            <span class="font-display text-base tracking-wide">{{ $entry['winners'][0] ?? '?' }}</span>
                            <span class="font-mono text-[11px] text-subtle">
                                {{ count($entry['winners']) }} lot{{ count($entry['winners']) > 1 ? 's' : '' }}
                                · {{ count($entry['participants']) }} participant{{ count($entry['participants']) > 1 ? 's' : '' }}
                            </span>
                        </div>
                        @break

                        @case('number_roulette')
                        <div class="flex flex-wrap items-center gap-2.5">
                            <span @class([
                                'flex h-6 w-6 shrink-0 items-center justify-center rounded-full border border-ink/30 font-mono text-[10px] font-bold',
                                'bg-emerald-600 text-white' => $entry['color'] === 'green',
                                'bg-red-600 text-white' => $entry['color'] === 'red',
                                'bg-ink text-white' => $entry['color'] === 'black',
                                ])>{{ $entry['result'] }}</span>
                            <span class="text-sm text-ink">{{ $entry['bet_type_label'] }}</span>
                            <span class="font-mono text-xs text-subtle">mise {{ $entry['stake'] }}</span>
                            <span @class([
                                'rounded-md border px-1.5 py-0.5 font-mono text-[10px] font-bold uppercase',
                                'border-ink bg-secondary text-ink' => $entry['won'],
                                'border-danger/30 bg-danger/10 text-danger' => ! $entry['won'],
                                ])>
                                {{ $entry['payout'] > 0 ? '+' : '' }}{{ $entry['payout'] }}
                            </span>
                        </div>
                        @break
                        @endswitch
                    </div>

                    @if($isWheelMode)
                    @php $modeLabel = \App\Application\Home\Enums\GameModeType::from($entry['mode'])->toDto()->title; @endphp
                    <button type="button" x-on:click="openEntry = Object.assign({}, @js($entry), { modeLabel: @js($modeLabel) })" class="shrink-0 rounded-lg border-2 border-ink bg-wash px-2.5 py-1.5 font-mono text-[10px] uppercase tracking-widest text-subtle transition hover:border-ink hover:bg-ink hover:text-white">
                        Détails
                    </button>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="rounded-xl border-2 border-dashed border-line py-12 text-center">
            <p class="mb-1 text-2xl">🗂️</p>
            <p class="text-sm font-medium text-muted">Aucun tirage pour l’instant</p>
            <p class="mt-0.5 text-xs text-faint">Lancez un tirage sur l’un des modes pour le voir apparaître ici</p>
        </div>
        @endif
    </div>

    <x-history.details-modal />
</div>
