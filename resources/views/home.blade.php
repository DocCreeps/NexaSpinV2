<x-layouts.app :title="$title" :meta-description="$metaDescription">
    <div class="min-h-screen w-full bg-[#FBF7F0] text-[#2B2620] antialiased selection:bg-[#E8623D] selection:text-white" x-data="{
            activeFilter: 'all',
            showHero: true,
            filters: [
                { id: 'all', label: 'Tout' },
                @foreach($modeGroups as $group)
                { id: '{{ $group['category']->value }}', label: '{{ $group['category']->label() }}' },
                @endforeach
            ],
            selectFilter(id) {
                this.activeFilter = id;
                this.showHero = false;
            },
            scrollToModes() {
                this.showHero = false;
                this.$nextTick(() => {
                    document.getElementById('modes-section')?.scrollIntoView({ behavior: 'smooth' });
                });
            }
        }">

        {{-- ========== HÉRO ========== --}}
        <section x-show="showHero" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="relative flex min-h-dvh w-full flex-col justify-between border-b border-[#E8E1D3] px-4 sm:px-8 py-6 sm:py-8">

            <div class="pointer-events-none absolute inset-0 -z-10 opacity-60 [background-image:radial-gradient(#E8E1D3_1px,transparent_1px)] [background-size:28px_28px]"></div>

            <div class="mx-auto flex w-full max-w-6xl items-center justify-between border-b border-[#E8E1D3] pb-4 font-mono">
                <div class="flex items-center gap-2.5 sm:gap-3">
                    <img src="{{ asset('images/logo.png') }}" alt="NexaSpin" class="h-7 sm:h-8 w-auto object-contain">
                    <span class="text-lg sm:text-xl font-semibold tracking-wide">NEXASPIN</span>
                </div>

                <span class="inline-flex items-center gap-2 rounded-full border border-[#E8E1D3] bg-white px-3 py-1.5 text-[11px] sm:text-xs text-[#8A8375]">
                    <span class="h-1.5 w-1.5 rounded-full bg-[#E8623D]"></span>
                    <span class="hidden sm:inline">GRATUIT — SANS INSCRIPTION</span>
                    <span class="sm:hidden">GRATUIT</span>
                </span>
            </div>

            <div class="mx-auto my-auto w-full max-w-3xl text-center py-6 sm:py-8">
                <p class="font-mono text-xs tracking-[0.3em] text-[#B0A996] mb-4">NexaSpin GÉNÉRATEUR DE DÉCISION ALÉATOIRE</p>

                <h1 class="font-display text-3xl font-bold tracking-tight sm:text-6xl leading-[1.15] sm:leading-[1.1] text-[#2B2620]">
                    Tranchez vos choix <br class="hidden sm:inline" />
                    <span class="text-[#E8623D]">sans perdre de temps.</span>
                </h1>

                <p class="mt-4 sm:mt-6 text-sm sm:text-lg leading-relaxed text-[#8A8375] max-w-2xl mx-auto px-2">
                    Choix du restaurant entre collègues, amis, arbitrage d'un concours entre amis ou prise de décision rapide : sélectionnez l'outil idéal et laissez un tirage impartial trancher vos hésitations.
                </p>

                <div class="mt-6 sm:mt-8">
                    <button type="button" @click="scrollToModes()" class="group w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-full bg-[#E8623D] px-8 py-3.5 sm:py-4 font-mono text-sm font-bold text-white shadow-lg shadow-[#E8623D]/20 hover:bg-[#D9532E] focus:outline-none transition-colors duration-150">
                        <span>LANCER UN TIRAGE</span>
                        <span class="transition-transform duration-150 group-hover:translate-y-0.5">&darr;</span>
                    </button>
                </div>

                {{-- ========== STATS ANIMÉES (remplace le bloc de badges) ========== --}}
                <div class="mt-8 sm:mt-14 grid grid-cols-3 gap-3 sm:gap-6 max-w-xl mx-auto">

                    {{-- Nombre de modes --}}
                    <div class="rounded-2xl border border-[#E8E1D3] bg-white px-3 py-5 sm:py-6 text-center shadow-sm shadow-black/[0.03]" x-data="{
            display: 0,
            target: {{ collect($modeGroups)->flatMap(fn($g) => $g['modes'])->count() }}
        }" x-init="
            let start = null;
            const duration = 1000;
            const step = (ts) => {
                if (!start) start = ts;
                const progress = Math.min((ts - start) / duration, 1);
                const eased = 1 - Math.pow(1 - progress, 3);
                display = Math.round(eased * target);
                if (progress < 1) requestAnimationFrame(step);
            };
            requestAnimationFrame(step);
        ">
                        <p class="font-mono text-2xl sm:text-3xl font-bold text-[#2B2620]" x-text="display"></p>
                        <p class="mt-1 font-mono text-[10px] sm:text-xs uppercase tracking-wider text-[#8A8375]">Modes</p>
                    </div>

                    {{-- Impartialité --}}
                    <div class="rounded-2xl border border-[#E8E1D3] bg-white px-3 py-5 sm:py-6 text-center shadow-sm shadow-black/[0.03]" x-data="{ display: 0, target: 100 }" x-init="
            let start = null;
            const duration = 1000;
            const step = (ts) => {
                if (!start) start = ts;
                const progress = Math.min((ts - start) / duration, 1);
                const eased = 1 - Math.pow(1 - progress, 3);
                display = Math.round(eased * target);
                if (progress < 1) requestAnimationFrame(step);
            };
            requestAnimationFrame(step);
        ">
                        <p class="font-mono text-2xl sm:text-3xl font-bold text-[#2B2620]"><span x-text="display"></span>%</p>
                        <p class="mt-1 font-mono text-[10px] sm:text-xs uppercase tracking-wider text-[#8A8375]">Impartial</p>
                    </div>

                    {{-- Sans compte --}}
                    <div class="rounded-2xl border border-[#E8E1D3] bg-white px-3 py-5 sm:py-6 text-center shadow-sm shadow-black/[0.03]" x-data="{ display: 0 }" x-init="setTimeout(() => display = 0, 400)">
                        <p class="font-mono text-2xl sm:text-3xl font-bold text-[#2B2620]"><span x-text="display"></span>€</p>
                        <p class="mt-1 font-mono text-[10px] sm:text-xs uppercase tracking-wider text-[#8A8375]">Sans compte</p>
                    </div>

                </div>

            </div>

            <div class="mx-auto flex w-full max-w-6xl items-center justify-between border-t border-[#E8E1D3] pt-4 font-mono text-[10px] sm:text-xs text-[#8A8375]">
                <span class="tracking-[0.3em]">CHOISISSEZ UN MODE</span>
                <button type="button" @click="scrollToModes()" class="font-semibold text-[#8A8375] hover:text-[#E8623D] transition-colors focus:outline-none">
                    CATALOGUE &darr;
                </button>
            </div>
        </section>

        {{-- ========== CATALOGUE ========== --}}
        <main id="modes-section" class="mx-auto max-w-6xl px-4 sm:px-8 py-8 sm:py-12">

            <div class="mb-8 sm:mb-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <h2 class="font-display text-lg sm:text-xl font-bold">
                    Catalogue des tirages
                </h2>

                <button type="button" @click="showHero = true; $nextTick(() => window.scrollTo({ top: 0, behavior: 'smooth' }))" class="inline-flex items-center gap-1.5 self-start md:self-auto font-mono text-xs text-[#8A8375] hover:text-[#E8623D] transition-colors focus:outline-none">
                    <span>&uarr;</span>
                    REVOIR LA PRÉSENTATION
                </button>
            </div>

           {{-- ========== SECTION FILTRES ========== --}}
           <div x-data="{ filtersOpen: false }" class="mb-8 sm:mb-10">
               <div class="flex items-center justify-between mb-3">
                   <p class="font-mono text-[10px] sm:text-xs uppercase tracking-wider text-[#B0A996]">
                       Filtrer par catégorie
                   </p>
               </div>

               {{-- Pills inline — desktop / tablette --}}
               <div class="hidden sm:flex flex-wrap gap-2.5 border-b border-[#E8E1D3] pb-5">
                   <button type="button" @click="selectFilter('all')" :class="activeFilter === 'all'
                ? 'bg-[#2B2620] border-[#2B2620] text-white'
                : 'bg-white border-[#E8E1D3] text-[#8A8375] hover:border-[#D9CFB8] hover:text-[#2B2620]'" class="inline-flex items-center gap-2 rounded-full border px-4 py-2 font-mono text-xs font-semibold uppercase tracking-wide transition-colors duration-150 focus:outline-none">
                       Tout
                       <span :class="activeFilter === 'all' ? 'bg-white/20 text-white' : 'bg-[#F5F0E6] text-[#8A8375]'" class="rounded-full px-1.5 py-0.5 text-[10px] leading-none">{{ collect($modeGroups)->flatMap(fn($g) => $g['modes'])->count() }}</span>
                   </button>

                   @foreach($modeGroups as $group)
                   <button type="button" @click="selectFilter('{{ $group['category']->value }}')" :class="activeFilter === '{{ $group['category']->value }}'
                ? 'bg-[#E8623D] border-[#E8623D] text-white'
                : 'bg-white border-[#E8E1D3] text-[#8A8375] hover:border-[#D9CFB8] hover:text-[#2B2620]'" class="inline-flex items-center gap-2 rounded-full border px-4 py-2 font-mono text-xs font-semibold uppercase tracking-wide transition-colors duration-150 focus:outline-none">
                       {{ $group['category']->label() }}
                       <span :class="activeFilter === '{{ $group['category']->value }}' ? 'bg-white/20 text-white' : 'bg-[#F5F0E6] text-[#8A8375]'" class="rounded-full px-1.5 py-0.5 text-[10px] leading-none">{{ count($group['modes']) }}</span>
                   </button>
                   @endforeach
               </div>

               {{-- Badge du filtre actif — mobile uniquement --}}
               <div class="sm:hidden border-b border-[#E8E1D3] pb-5">
                   <button type="button" @click="filtersOpen = true" class="inline-flex items-center gap-2 rounded-full border border-[#E8E1D3] bg-white px-4 py-2 font-mono text-xs font-semibold text-[#2B2620]">
                       <span x-text="filters.find(f => f.id === activeFilter)?.label"></span>
                       <span class="text-[#E8623D]">&darr;</span>
                   </button>
               </div>

               {{-- Burger flottant — mobile uniquement, caché sur le hero --}}
               <button type="button" x-show="!showHero" x-cloak x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100" @click="filtersOpen = true" class="sm:hidden fixed top-4 right-4 z-40 flex h-12 w-12 items-center justify-center rounded-full bg-white border border-[#E8E1D3] shadow-lg shadow-black/[0.06] focus:outline-none" aria-label="Filtrer par catégorie">
                   <span class="relative flex flex-col gap-1">
                       <span class="h-0.5 w-5 bg-[#2B2620]"></span>
                       <span class="h-0.5 w-5 bg-[#2B2620]"></span>
                       <span class="h-0.5 w-3 bg-[#2B2620]"></span>
                       <span x-show="activeFilter !== 'all'" x-cloak class="absolute -top-1 -right-1.5 h-2 w-2 rounded-full bg-[#E8623D]"></span>
                   </span>
               </button>


               {{-- Panneau de filtres — mobile, plein écran depuis la droite --}}
               <div x-show="filtersOpen" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="sm:hidden fixed inset-0 z-50 bg-[#2B2620]/40" @click.self="filtersOpen = false">
                   <div x-show="filtersOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full" class="absolute right-0 top-0 h-full w-[85%] max-w-xs bg-[#FBF7F0] p-5 shadow-xl">
                       <div class="flex items-center justify-between mb-6">
                           <p class="font-mono text-xs uppercase tracking-wider text-[#8A8375]">Catégories</p>
                           <button type="button" @click="filtersOpen = false" class="flex h-8 w-8 items-center justify-center rounded-full border border-[#E8E1D3] bg-white text-[#8A8375]" aria-label="Fermer">
                               &times;
                           </button>
                       </div>

                       <div class="flex flex-col gap-2">
                           <button type="button" @click="selectFilter('all'); filtersOpen = false" :class="activeFilter === 'all' ? 'bg-[#2B2620] border-[#2B2620] text-white' : 'bg-white border-[#E8E1D3] text-[#2B2620]'" class="flex items-center justify-between rounded-xl border px-4 py-3 font-mono text-sm font-semibold">
                               <span>Tout</span>
                               <span :class="activeFilter === 'all' ? 'bg-white/20 text-white' : 'bg-[#F5F0E6] text-[#8A8375]'" class="rounded-full px-2 py-0.5 text-xs">{{ collect($modeGroups)->flatMap(fn($g) => $g['modes'])->count() }}</span>
                           </button>

                           @foreach($modeGroups as $group)
                           <button type="button" @click="selectFilter('{{ $group['category']->value }}'); filtersOpen = false" :class="activeFilter === '{{ $group['category']->value }}' ? 'bg-[#E8623D] border-[#E8623D] text-white' : 'bg-white border-[#E8E1D3] text-[#2B2620]'" class="flex items-center justify-between rounded-xl border px-4 py-3 font-mono text-sm font-semibold">
                               <span>{{ $group['category']->label() }}</span>
                               <span :class="activeFilter === '{{ $group['category']->value }}' ? 'bg-white/20 text-white' : 'bg-[#F5F0E6] text-[#8A8375]'" class="rounded-full px-2 py-0.5 text-xs">{{ count($group['modes']) }}</span>
                           </button>
                           @endforeach
                       </div>
                   </div>
               </div>
           </div>

            {{-- Compteur global au lieu de $loop->iteration imbriqué --}}
            @php $globalIndex = 0; @endphp

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 sm:gap-6">
                @foreach($modeGroups as $group)
                @foreach($group['modes'] as $mode)
                @php $globalIndex++; @endphp
                <div x-show="activeFilter === 'all' || activeFilter === '{{ $group['category']->value }}'" x-cloak x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="h-full">
                    <x-mode-card :mode="$mode" :category="$group['category']->label()" :index="$globalIndex" />
                </div>
                @endforeach
                @endforeach
            </div>

            <footer class="mt-12 sm:mt-16 border-t border-[#E8E1D3] pt-6 text-center font-mono text-xs text-[#8A8375]">
                NEXASPIN — OUTIL DE TIRAGE AU SORT
            </footer>
        </main>



    </div>
</x-layouts.app>
