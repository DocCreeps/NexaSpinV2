<x-layouts.app :title="$title" :meta-description="$metaDescription">
    <div class="min-h-screen w-full bg-surface text-ink antialiased selection:bg-secondary selection:text-ink" x-data="{
            activeFilter: 'all',
            showHero: true,
            heroVisible: true,
            filtersOpen: false,
            filters: [
                { id: 'all', label: 'Tout' },
                @foreach($modeGroups as $group)
                { id: '{{ $group['category']->value }}', label: '{{ $group['category']->label() }}' },
                @endforeach
            ],
            selectFilter(id) { this.activeFilter = id; },
            hideHero() {
                this.showHero = false;
                this.$nextTick(() => window.scrollTo({ top: 0, behavior: 'smooth' }));
            },
            revealHero() {
                this.showHero = true;
                this.$nextTick(() => window.scrollTo({ top: 0, behavior: 'smooth' }));
            },
            closeFilters() {
                this.filtersOpen = false;
                this.$refs.filterTrigger?.focus();
            },
            init() {
                const hero = this.$refs.hero;
                if (!hero) return;
                new IntersectionObserver(
                    (entries) => { this.heroVisible = entries[0].isIntersecting; },
                    { threshold: 0.15 }
                ).observe(hero);
            }
        }" @keydown.escape.window="if (filtersOpen) closeFilters()">

        {{-- ========== HÉRO ========== --}}
        <section x-ref="hero" x-show="showHero" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" aria-label="Présentation de NexaSpin" class="relative flex min-h-[100dvh] w-full flex-col justify-between overflow-hidden border-b-4 border-ink px-4 py-5 sm:min-h-dvh sm:px-10 sm:py-8">
            <div class="pointer-events-none absolute inset-0 -z-10 opacity-[0.06] [background-image:radial-gradient(var(--color-ink)_1.5px,transparent_1.5px)] [background-size:22px_22px]" aria-hidden="true"></div>

            {{-- Top bar --}}
            <div class="mx-auto flex w-full max-w-6xl items-center justify-between pb-3 sm:pb-4">
                <div class="flex items-center gap-2 sm:gap-3">
                    <img src="{{ asset('images/logo.png') }}" alt="NexaSpin" class="h-7 w-auto object-contain sm:h-8">
                    <span class="font-display text-lg tracking-tight sm:text-xl">NexaSpin</span>
                </div>

                <span class="inline-flex items-center rounded-md border-2 border-ink bg-secondary px-2.5 py-1 sm:px-3 sm:py-1.5">
                    <span class="font-mono text-[9px] leading-none sm:text-[10px]">FREE PLAY</span>
                </span>
            </div>

            {{-- Contenu central --}}
            <div class="mx-auto my-auto w-full max-w-3xl py-4 text-center sm:py-8">
                <p class="mb-3 font-mono text-[10px] tracking-widest text-subtle sm:mb-5 sm:text-xs">
                    <span aria-hidden="true">◆</span> GÉNÉRATEUR DE DÉCISION ALÉATOIRE <span aria-hidden="true">◆</span>
                </p>

                <h1 class="font-display text-[1.75rem] leading-[1.2] text-ink xs:text-3xl sm:text-6xl sm:leading-[1.15]">
                    Tranchez vos choix
                    <br class="hidden sm:inline" />
                    <span class="text-outline text-secondary">sans perdre de temps.</span>
                </h1>

                <p class="mx-auto mt-4 max-w-2xl px-1 text-sm leading-relaxed text-muted sm:mt-7 sm:px-2 sm:text-lg">
                    Choix du restaurant, arbitrage entre amis ou décision rapide :
                    sélectionnez l’outil et laissez un tirage impartial trancher.
                </p>

                <div class="mt-6 sm:mt-10">
                    <button type="button" @click="hideHero()" class="btn-press inline-flex w-full items-center justify-center gap-2 rounded-xl border-2 border-ink bg-primary px-8 py-3.5 font-display text-xs text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-info focus-visible:ring-offset-2 sm:w-auto sm:py-4 sm:text-sm">
                        <span aria-hidden="true">▶</span> LANCER UN TIRAGE
                    </button>

                    <p class="mt-3 text-xs text-subtle sm:mt-4 sm:text-sm">
                        Départager un débat, choisir un resto, ou trancher un jeu.
                    </p>
                </div>

                {{-- Stats --}}
                <div class="mx-auto mt-6 grid max-w-lg grid-cols-3 gap-2 sm:mt-10 sm:gap-5">
                    <div class="card-hard rounded-xl border-2 border-ink bg-panel px-2 py-3 text-center sm:px-3 sm:py-5" x-data="{
                            display: 0,
                            target: {{ collect($modeGroups)->flatMap(fn ($g) => $g['modes'])->where('available', true)->count() }}
                        }" x-init="
                            let start = null;
                            const duration = 900;
                            const step = (ts) => {
                                if (!start) start = ts;
                                const progress = Math.min((ts - start) / duration, 1);
                                const eased = 1 - Math.pow(1 - progress, 3);
                                display = Math.round(eased * target);
                                if (progress < 1) requestAnimationFrame(step);
                            };
                            requestAnimationFrame(step);
                        ">
                        <p class="font-display text-xl text-ink sm:text-3xl" x-text="display"></p>
                        <p class="mt-1 font-mono text-[8px] uppercase tracking-widest text-subtle sm:text-[10px]">Modes</p>
                    </div>

                    <div class="card-hard rounded-xl border-2 border-ink bg-panel px-2 py-3 text-center sm:px-3 sm:py-5" x-data="{ display: 0, target: 100 }" x-init="
                            let start = null;
                            const duration = 900;
                            const step = (ts) => {
                                if (!start) start = ts;
                                const progress = Math.min((ts - start) / duration, 1);
                                const eased = 1 - Math.pow(1 - progress, 3);
                                display = Math.round(eased * target);
                                if (progress < 1) requestAnimationFrame(step);
                            };
                            requestAnimationFrame(step);
                        ">
                        <p class="font-display text-xl text-ink sm:text-3xl"><span x-text="display"></span>%</p>
                        <p class="mt-1 font-mono text-[8px] uppercase tracking-widest text-subtle sm:text-[10px]">Gratuit</p>
                    </div>

                    <div class="card-hard rounded-xl border-2 border-ink bg-panel px-2 py-3 text-center sm:px-3 sm:py-5">
                        <p class="font-display text-xl text-ink sm:text-3xl">0€</p>
                        <p class="mt-1 font-mono text-[8px] uppercase tracking-widest text-subtle sm:text-[10px]">Sans compte</p>
                    </div>
                </div>
            </div>

            {{-- Bas hero --}}
            <div class="mx-auto flex w-full max-w-6xl items-center justify-between gap-3 border-t-2 border-dashed border-line pt-3 text-xs text-muted sm:pt-4 sm:text-sm">
                <span class="min-w-0 truncate">Choisissez un mode ci-dessous</span>
                <button type="button" @click="hideHero()" class="shrink-0 rounded font-semibold text-primary transition-opacity hover:opacity-70 focus:outline-none focus-visible:ring-2 focus-visible:ring-info focus-visible:ring-offset-2">
                    Catalogue <span aria-hidden="true">▸</span>
                </button>
            </div>
        </section>

        {{-- Burger filtres mobile --}}
        <button type="button" x-ref="filterTrigger" x-show="!heroVisible" x-cloak x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100" @click="filtersOpen = true" :aria-expanded="filtersOpen.toString()" aria-haspopup="dialog" aria-controls="filters-dialog" class="fixed top-4 right-4 z-40 flex h-11 w-11 items-center justify-center rounded-xl border-2 border-ink bg-secondary shadow-hard focus:outline-none focus-visible:ring-2 focus-visible:ring-info focus-visible:ring-offset-2 sm:hidden" aria-label="Filtrer par catégorie">
            <span class="relative flex flex-col gap-1" aria-hidden="true">
                <span class="h-0.5 w-5 bg-ink"></span>
                <span class="h-0.5 w-5 bg-ink"></span>
                <span class="h-0.5 w-3 bg-ink"></span>
                <span x-show="activeFilter !== 'all'" x-cloak class="absolute -top-1 -right-1.5 h-2 w-2 rounded-full bg-primary"></span>
            </span>
        </button>

        {{-- ========== CATALOGUE ========== --}}
        <main id="modes-section" class="mx-auto max-w-6xl scroll-mt-4 px-4 py-6 sm:px-10 sm:py-12">

            <div class="mb-6 flex flex-col justify-between gap-3 sm:mb-10 sm:gap-4 md:flex-row md:items-center">
                <div>
                    <h2 class="font-display text-lg text-ink sm:text-xl">SELECT MODE</h2>
                    <div class="mt-2 flex gap-1.5" aria-hidden="true">
                        <span class="h-1.5 w-6 rounded-full bg-primary"></span>
                        <span class="h-1.5 w-6 rounded-full bg-info"></span>
                        <span class="h-1.5 w-6 rounded-full bg-secondary"></span>
                    </div>
                </div>

                <button type="button" @click="revealHero()" class="inline-flex items-center gap-1.5 self-start rounded text-sm text-muted transition-colors hover:text-ink focus:outline-none focus-visible:ring-2 focus-visible:ring-info focus-visible:ring-offset-2 md:self-auto">
                    <span aria-hidden="true">↑</span> Revoir la présentation
                </button>
            </div>

            {{-- Filtres --}}
            <div class="mb-6 sm:mb-10" role="group" aria-label="Filtrer les modes par catégorie">
                <p class="mb-3 font-mono text-[10px] tracking-widest text-faint">FILTRER</p>

                {{-- Desktop --}}
                <div class="hidden flex-wrap gap-2.5 border-b-2 border-ink/10 pb-5 sm:flex">
                    <button type="button" @click="selectFilter('all')" :aria-pressed="(activeFilter === 'all').toString()" :class="activeFilter === 'all'
                            ? 'bg-ink border-ink text-white'
                            : 'bg-panel border-ink text-muted hover:bg-wash'" class="inline-flex items-center gap-1.5 rounded-lg border-2 px-4 py-2 text-sm font-semibold transition-colors duration-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-info focus-visible:ring-offset-2">
                        <span x-show="activeFilter === 'all'" x-cloak aria-hidden="true">▶</span>
                        Tout
                        <span class="text-xs opacity-60">({{ collect($modeGroups)->flatMap(fn ($g) => $g['modes'])->count() }})</span>
                    </button>

                    @foreach($modeGroups as $group)
                    <button type="button" @click="selectFilter('{{ $group['category']->value }}')" :aria-pressed="(activeFilter === '{{ $group['category']->value }}').toString()" :class="activeFilter === '{{ $group['category']->value }}'
                                ? 'bg-ink border-ink text-white'
                                : 'bg-panel border-ink text-muted hover:bg-wash'" class="inline-flex items-center gap-1.5 rounded-lg border-2 px-4 py-2 text-sm font-semibold transition-colors duration-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-info focus-visible:ring-offset-2">
                        <span x-show="activeFilter === '{{ $group['category']->value }}'" x-cloak aria-hidden="true">▶</span>
                        {{ $group['category']->label() }}
                        <span class="text-xs opacity-60">({{ count($group['modes']) }})</span>
                    </button>
                    @endforeach
                </div>

                {{-- Mobile trigger --}}
                <div class="border-b-2 border-ink/10 pb-4 sm:hidden">
                    <button type="button" @click="filtersOpen = true" :aria-expanded="filtersOpen.toString()" aria-haspopup="dialog" aria-controls="filters-dialog" class="inline-flex w-full items-center justify-between rounded-lg border-2 border-ink bg-panel px-4 py-2.5 text-sm font-semibold focus:outline-none focus-visible:ring-2 focus-visible:ring-info focus-visible:ring-offset-2">
                        <span x-text="filters.find(f => f.id === activeFilter)?.label"></span>
                        <span aria-hidden="true">▾</span>
                    </button>
                </div>

                {{-- Drawer mobile --}}
                <div id="filters-dialog" x-show="filtersOpen" x-cloak role="dialog" aria-modal="true" aria-label="Filtrer par catégorie" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 bg-ink/50 sm:hidden" @click.self="closeFilters()">
                    <div x-show="filtersOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full" class="absolute right-0 top-0 h-full w-[min(85%,20rem)] border-l-2 border-ink bg-surface p-5">
                        <div class="mb-6 flex items-center justify-between">
                            <p class="font-mono text-[10px] tracking-widest text-muted">CATÉGORIES</p>
                            <button type="button" @click="closeFilters()" class="flex h-9 w-9 items-center justify-center rounded-lg border-2 border-ink bg-panel focus:outline-none focus-visible:ring-2 focus-visible:ring-info" aria-label="Fermer les filtres">
                                <span aria-hidden="true">✕</span>
                            </button>
                        </div>

                        <div class="flex flex-col gap-2">
                            <button type="button" @click="selectFilter('all'); closeFilters()" :aria-pressed="(activeFilter === 'all').toString()" :class="activeFilter === 'all' ? 'bg-ink border-ink text-white' : 'bg-panel border-ink text-ink'" class="flex items-center justify-between rounded-lg border-2 px-4 py-3 text-sm font-semibold focus:outline-none focus-visible:ring-2 focus-visible:ring-info">
                                <span>Tout</span>
                                <span class="text-xs opacity-60">{{ collect($modeGroups)->flatMap(fn ($g) => $g['modes'])->count() }}</span>
                            </button>

                            @foreach($modeGroups as $group)
                            <button type="button" @click="selectFilter('{{ $group['category']->value }}'); closeFilters()" :aria-pressed="(activeFilter === '{{ $group['category']->value }}').toString()" :class="activeFilter === '{{ $group['category']->value }}' ? 'bg-ink border-ink text-white' : 'bg-panel border-ink text-ink'" class="flex items-center justify-between rounded-lg border-2 px-4 py-3 text-sm font-semibold focus:outline-none focus-visible:ring-2 focus-visible:ring-info">
                                <span>{{ $group['category']->label() }}</span>
                                <span class="text-xs opacity-60">{{ count($group['modes']) }}</span>
                            </button>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- Grille modes --}}
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 sm:gap-6 lg:grid-cols-3 lg:gap-7">
                @foreach($modeGroups as $group)
                @foreach($group['modes'] as $mode)
                <div x-show="activeFilter === 'all' || activeFilter === '{{ $group['category']->value }}'" x-cloak x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="h-full">
                    <x-mode-card :mode="$mode" :category="$group['category']->label()" />
                </div>
                @endforeach
                @endforeach
            </div>

            <footer class="mt-10 border-t-2 border-ink/10 pt-5 text-center font-mono text-[10px] tracking-widest text-faint sm:mt-16 sm:pt-6">
                NEXASPIN — GAME OVER? INSERT DECISION TO CONTINUE
            </footer>
        </main>
    </div>
</x-layouts.app>
