<x-layouts.app :title="$title" :meta-description="$metaDescription">
    <div class="min-h-screen w-full bg-[#FAFAF8] text-[#17171B] antialiased selection:bg-[#FFC531] selection:text-[#17171B]" x-data="{
            activeFilter: 'all',
            showHero: true,
            filtersOpen: false,
            filters: [
                { id: 'all', label: 'Tout' },
                @foreach($modeGroups as $group)
                { id: '{{ $group['category']->value }}', label: '{{ $group['category']->label() }}' },
                @endforeach
            ],
            selectFilter(id) { this.activeFilter = id; },
            scrollToModes() {
                this.$nextTick(() => {
                    const el = document.getElementById('modes-section');
                    if (!el) return;
                    const top = el.getBoundingClientRect().top + window.scrollY - 16;
                    window.scrollTo({ top, behavior: 'smooth' });
                });
            },
            scrollToTop() { window.scrollTo({ top: 0, behavior: 'smooth' }); },
            init() {
                const hero = this.$refs.hero;
                if (!hero) return;
                new IntersectionObserver(
                    (entries) => { this.showHero = entries[0].isIntersecting; },
                    { threshold: 0.2 }
                ).observe(hero);
            }
        }">

        <style>
            .btn-arcade {
                box-shadow: 0 6px 0 #A8210F;
                transition: transform .08s ease, box-shadow .08s ease;
            }

            .btn-arcade:active {
                transform: translateY(6px);
                box-shadow: 0 0 0 #A8210F;
            }

            .card-cart {
                box-shadow: 4px 4px 0 0 #17171B;
                transition: transform .12s ease, box-shadow .12s ease;
            }

            .card-cart:hover,
            .card-cart:focus-visible {
                transform: translate(-3px, -3px);
                box-shadow: 7px 7px 0 0 #17171B;
            }

            .text-outline {
                -webkit-text-stroke: 2px #17171B;
                paint-order: stroke fill;
            }

        </style>

        {{-- ========== HÉRO ========== --}}
        <section x-ref="hero" class="relative flex min-h-dvh w-full flex-col justify-between overflow-hidden border-b-4 border-[#17171B] px-4 sm:px-10 py-6 sm:py-8">

            {{-- Panneau perforé, façon façade de borne --}}
            <div class="pointer-events-none absolute inset-0 -z-10 opacity-[0.06] [background-image:radial-gradient(#17171B_1.5px,transparent_1.5px)] [background-size:22px_22px]"></div>

            <div class="mx-auto flex w-full max-w-6xl items-center justify-between pb-4">
                <div class="flex items-center gap-2.5 sm:gap-3">
                    <img src="{{ asset('images/logo.png') }}" alt="NexaSpin" class="h-7 sm:h-8 w-auto object-contain">
                    <span class="font-display text-lg sm:text-xl tracking-tight">NexaSpin</span>
                </div>

                <span class="inline-flex items-center gap-2 rounded-md border-2 border-[#17171B] bg-[#FFC531] px-3 py-1.5">
                    <span class="font-mono text-[9px] sm:text-[10px] leading-none">FREE PLAY</span>
                </span>
            </div>

            <div class="mx-auto my-auto w-full max-w-3xl py-6 sm:py-8 text-center">
                <p class="font-mono text-[10px] sm:text-xs tracking-widest text-[#7A756B] mb-5">
                    ◆ GÉNÉRATEUR DE DÉCISION ALÉATOIRE ◆
                </p>

                <h1 class="font-display text-3xl sm:text-6xl leading-[1.25] sm:leading-[1.15] text-[#17171B]">
                    Tranchez vos choix<br class="hidden sm:inline" />
                    <span class="text-outline text-[#FFC531]">sans perdre de temps.</span>
                </h1>

                <p class="mt-6 sm:mt-7 text-sm sm:text-lg leading-relaxed text-[#5A564D] max-w-2xl mx-auto px-2">
                    Choix du restaurant entre collègues, amis, arbitrage d'un concours entre amis ou prise de décision rapide : sélectionnez l'outil idéal et laissez un tirage impartial trancher vos hésitations.
                </p>

                <div class="mt-8 sm:mt-10">
                    <button type="button" @click="scrollToModes()" class="btn-arcade group w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-xl border-2 border-[#17171B] bg-[#E8291C] px-8 py-3.5 sm:py-4 font-display text-xs sm:text-sm text-white focus:outline-none">
                        <span>▶ LANCER UN TIRAGE</span>
                    </button>

                    <p class="mt-4 text-sm text-[#7A756B]">
                        Départager un débat, choisir un resto, ou trancher un jeu entre amis.
                    </p>
                </div>
            </div>

            <div class="mx-auto flex w-full max-w-6xl items-center justify-between border-t-2 border-dashed border-[#D8D3C6] pt-4 text-xs sm:text-sm text-[#5A564D]">
                <span>Choisissez un mode ci-dessous</span>
                <button type="button" @click="scrollToModes()" class="font-semibold text-[#E8291C] hover:opacity-70 transition-opacity focus:outline-none">
                    Voir le catalogue ▸
                </button>
            </div>
        </section>

        {{-- Burger flottant — mobile, suit le scroll réel --}}
        <button type="button" x-show="!showHero" x-cloak x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100" @click="filtersOpen = true" class="sm:hidden fixed top-4 right-4 z-40 flex h-12 w-12 items-center justify-center rounded-xl border-2 border-[#17171B] bg-[#FFC531] shadow-[3px_3px_0_0_#17171B] focus:outline-none" aria-label="Filtrer par catégorie">
            <span class="relative flex flex-col gap-1">
                <span class="h-0.5 w-5 bg-[#17171B]"></span>
                <span class="h-0.5 w-5 bg-[#17171B]"></span>
                <span class="h-0.5 w-3 bg-[#17171B]"></span>
                <span x-show="activeFilter !== 'all'" x-cloak class="absolute -top-1 -right-1.5 h-2 w-2 rounded-full bg-[#E8291C]"></span>
            </span>
        </button>

        {{-- ========== CATALOGUE ========== --}}
        <main id="modes-section" class="scroll-mt-4 mx-auto max-w-6xl px-4 sm:px-10 py-8 sm:py-12">

            <div class="mb-8 sm:mb-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h2 class="font-display text-lg sm:text-xl text-[#17171B]">SELECT MODE</h2>
                    <div class="mt-2 flex gap-1.5" aria-hidden="true">
                        <span class="h-1.5 w-6 rounded-full bg-[#E8291C]"></span>
                        <span class="h-1.5 w-6 rounded-full bg-[#2F6FED]"></span>
                        <span class="h-1.5 w-6 rounded-full bg-[#FFC531]"></span>
                    </div>
                </div>

                <button type="button" @click="scrollToTop()" class="inline-flex items-center gap-1.5 self-start md:self-auto text-sm text-[#5A564D] hover:text-[#17171B] transition-colors focus:outline-none">
                    ↑ Revoir la présentation
                </button>
            </div>

            {{-- ========== FILTRES ========== --}}
            <div class="mb-8 sm:mb-10">
                <p class="mb-3 font-mono text-[10px] tracking-widest text-[#A39D8D]">FILTRER</p>

                <div class="hidden sm:flex flex-wrap gap-2.5 border-b-2 border-[#17171B]/10 pb-5">
                    <button type="button" @click="selectFilter('all')" :class="activeFilter === 'all'
                ? 'bg-[#17171B] border-[#17171B] text-white'
                : 'bg-white border-[#17171B] text-[#5A564D] hover:bg-[#F0EEE5]'" class="inline-flex items-center gap-1.5 rounded-lg border-2 px-4 py-2 text-sm font-semibold transition-colors duration-100 focus:outline-none">
                        <span x-show="activeFilter === 'all'" x-cloak>▶</span> Tout
                        <span class="opacity-60 text-xs">({{ collect($modeGroups)->flatMap(fn($g) => $g['modes'])->count() }})</span>
                    </button>

                    @foreach($modeGroups as $group)
                    <button type="button" @click="selectFilter('{{ $group['category']->value }}')" :class="activeFilter === '{{ $group['category']->value }}'
                ? 'bg-[#17171B] border-[#17171B] text-white'
                : 'bg-white border-[#17171B] text-[#5A564D] hover:bg-[#F0EEE5]'" class="inline-flex items-center gap-1.5 rounded-lg border-2 px-4 py-2 text-sm font-semibold transition-colors duration-100 focus:outline-none">
                        <span x-show="activeFilter === '{{ $group['category']->value }}'" x-cloak>▶</span> {{ $group['category']->label() }}
                        <span class="opacity-60 text-xs">({{ count($group['modes']) }})</span>
                    </button>
                    @endforeach
                </div>

                <div class="sm:hidden border-b-2 border-[#17171B]/10 pb-5">
                    <button type="button" @click="filtersOpen = true" class="inline-flex items-center gap-2 rounded-lg border-2 border-[#17171B] bg-white px-4 py-2 text-sm font-semibold">
                        <span x-text="filters.find(f => f.id === activeFilter)?.label"></span>
                        <span class="text-[#E8291C]">▾</span>
                    </button>
                </div>

                <div x-show="filtersOpen" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="sm:hidden fixed inset-0 z-50 bg-[#17171B]/50" @click.self="filtersOpen = false">
                    <div x-show="filtersOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full" class="absolute right-0 top-0 h-full w-[85%] max-w-xs bg-[#FAFAF8] border-l-2 border-[#17171B] p-5">
                        <div class="flex items-center justify-between mb-6">
                            <p class="font-mono text-[10px] tracking-widest text-[#5A564D]">CATÉGORIES</p>
                            <button type="button" @click="filtersOpen = false" class="flex h-8 w-8 items-center justify-center rounded-lg border-2 border-[#17171B] bg-white" aria-label="Fermer">✕</button>
                        </div>

                        <div class="flex flex-col gap-2">
                            <button type="button" @click="selectFilter('all'); filtersOpen = false" :class="activeFilter === 'all' ? 'bg-[#17171B] border-[#17171B] text-white' : 'bg-white border-[#17171B] text-[#17171B]'" class="flex items-center justify-between rounded-lg border-2 px-4 py-3 text-sm font-semibold">
                                <span>Tout</span>
                                <span class="opacity-60 text-xs">{{ collect($modeGroups)->flatMap(fn($g) => $g['modes'])->count() }}</span>
                            </button>

                            @foreach($modeGroups as $group)
                            <button type="button" @click="selectFilter('{{ $group['category']->value }}'); filtersOpen = false" :class="activeFilter === '{{ $group['category']->value }}' ? 'bg-[#17171B] border-[#17171B] text-white' : 'bg-white border-[#17171B] text-[#17171B]'" class="flex items-center justify-between rounded-lg border-2 px-4 py-3 text-sm font-semibold">
                                <span>{{ $group['category']->label() }}</span>
                                <span class="opacity-60 text-xs">{{ count($group['modes']) }}</span>
                            </button>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-7">
                @foreach($modeGroups as $group)
                @foreach($group['modes'] as $mode)
                <div x-show="activeFilter === 'all' || activeFilter === '{{ $group['category']->value }}'" x-cloak x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="h-full">
                    <x-mode-card :mode="$mode" :category="$group['category']->label()" />
                </div>
                @endforeach
                @endforeach
            </div>

            <footer class="mt-12 sm:mt-16 border-t-2 border-[#17171B]/10 pt-6 text-center font-mono text-[10px] tracking-widest text-[#A39D8D]">
                NEXASPIN — GAME OVER? INSERT DECISION TO CONTINUE
            </footer>
        </main>
    </div>
</x-layouts.app>
