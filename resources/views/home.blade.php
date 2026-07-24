<x-layouts.app :title="$title" :meta-description="$metaDescription">
    <!-- Plus de verrou de viewport (md:h-dvh + overflow-hidden) : avec un nombre de catégories
         variable, une page à hauteur fixe finissait par tronquer le contenu. La page défile
         naturellement dès que ça dépasse, sur toutes les tailles d'écran. -->
    <div class="min-h-dvh w-full overflow-x-hidden py-10 md:py-14 flex flex-col items-center bg-slate-50/50 relative antialiased selection:bg-indigo-500 selection:text-white">

        <div class="absolute -top-24 -left-24 w-96 h-96 bg-indigo-200/30 rounded-full blur-[100px] pointer-events-none"></div>
        <div class="absolute top-1/4 -right-24 w-72 h-72 bg-purple-200/20 rounded-full blur-[80px] pointer-events-none"></div>

        <div class="relative w-full max-w-6xl mx-auto px-6 flex flex-col gap-8">

            <div class="text-center max-w-3xl mx-auto space-y-3">
                <div class="inline-flex items-center justify-center w-14 h-14 lg:w-16 lg:h-16 rounded-2xl bg-white shadow-sm border border-slate-100 text-2xl lg:text-3xl transition hover:rotate-12 duration-300 select-none">
                    🎲
                </div>
                <h1 class="text-3xl lg:text-5xl font-black tracking-tight text-slate-900 leading-none">
                    Nexa<span class="bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">Spin</span>
                </h1>
                <p class="text-sm lg:text-base text-slate-600 font-medium max-w-xl mx-auto leading-relaxed">
                    Créez des tirages aléatoires, choisissez votre mode, et laissez la roue décider. 🚀
                </p>
            </div>

            <div class="flex items-center gap-4">
                <div class="h-px flex-1 bg-gradient-to-r from-transparent to-slate-200"></div>
                <span class="text-[9px] font-bold uppercase tracking-widest text-slate-400 select-none">
                    Choisissez votre expérience
                </span>
                <div class="h-px flex-1 bg-gradient-to-l from-transparent to-slate-200"></div>
            </div>

            {{--
                Catégories en colonnes : auto-fit calcule seul le nombre de colonnes selon la
                largeur disponible et le nombre de catégories (1 sur mobile, 2+ dès qu'elles
                tiennent). Ajouter une catégorie dans DrawModeCategory n'impose aucun réglage ici.

                Sur mobile, chaque catégorie est un accordéon (fermée sauf la première, on
                touche l'en-tête pour l'ouvrir) pour éviter une page à rallonge sur petit écran.
                À partir de md:, le contenu reste toujours visible quel que soit l'état — l'espace
                horizontal ne pose plus le même problème, et l'accordéon n'a plus lieu d'être.
            --}}
            <div
                class="grid grid-cols-1 md:grid-cols-[repeat(auto-fit,minmax(280px,1fr))] gap-8 items-start"
                x-data="{ openCategory: '{{ $modeGroups[0]['category']->value ?? '' }}' }"
                >
                @foreach($modeGroups as $group)
                @php $categoryValue = $group['category']->value; @endphp
                <section class="min-w-0">
                    <button
                        type="button"
                        @click="openCategory = openCategory === '{{ $categoryValue }}' ? null : '{{ $categoryValue }}'"
                        :aria-expanded="(openCategory === '{{ $categoryValue }}').toString()"
                        class="w-full flex items-center justify-between gap-2 mb-4 md:pointer-events-none md:cursor-default select-none"
                        >
                        <h2 class="text-xs font-bold uppercase tracking-widest text-slate-400">
                            {{ $group['category']->label() }}
                        </h2>
                        <svg
                            class="h-4 w-4 shrink-0 text-slate-400 transition-transform duration-200 md:hidden"
                            :class="openCategory === '{{ $categoryValue }}' ? 'rotate-180' : ''"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div :class="openCategory === '{{ $categoryValue }}' ? 'grid grid-cols-1 gap-4' : 'hidden md:grid md:grid-cols-1 gap-4'">
                        @foreach($group['modes'] as $mode)
                        <x-mode-card :mode="$mode" />
                        @endforeach
                    </div>
                </section>
                @endforeach
            </div>

            <div class="rounded-xl bg-white/60 backdrop-blur-sm border border-slate-200/60 py-3 px-4 text-center shadow-sm max-w-2xl mx-auto w-full">
                <p class="text-xs text-slate-500 font-medium">
                    🎉 Organisez vos concours, jeux, décisions aléatoires et tirages au sort en quelques secondes.
                </p>
            </div>

        </div>
    </div>
</x-layouts.app>
