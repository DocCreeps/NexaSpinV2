{{--
    Popup de détails d'un tirage de roue (participants / poids / éliminations).
    Ne prend aucun prop : entièrement piloté par `openEntry` dans le x-data
    du parent (null = fermé, sinon un objet { winner, participants, weights?,
    eliminations?, modeLabel }). Le parent doit fournir `openEntry` et est
    responsable de le peupler au clic (voir wheel-page.blade.php par ex.).
--}}
<div x-show="openEntry" x-cloak class="fixed inset-0 z-50 flex items-end justify-center p-0 sm:items-center sm:p-4">

    <div x-show="openEntry" x-on:click="openEntry = null" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="absolute inset-0 bg-ink/60 backdrop-blur-sm"></div>

    <div x-show="openEntry" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="card-hard relative max-h-[85vh] w-full max-w-md overflow-y-auto rounded-t-2xl border-2 border-ink bg-panel sm:rounded-2xl">
        <template x-if="openEntry">
            <div>
                {{-- En-tête --}}
                <div class="sticky top-0 flex items-start justify-between gap-3 rounded-t-2xl border-b-2 border-ink bg-secondary/20 px-5 py-4 sm:px-6">
                    <div class="min-w-0">
                        <p class="font-mono text-[10px] uppercase tracking-widest text-subtle" x-text="openEntry.modeLabel"></p>
                        <div class="mt-1 flex items-center gap-2">
                            <span class="text-2xl leading-none">🏆</span>
                            <p class="truncate font-display text-2xl leading-tight text-ink" x-text="openEntry.winner"></p>
                        </div>
                    </div>
                    <button type="button" x-on:click="openEntry = null" aria-label="Fermer" class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full border-2 border-ink bg-panel font-mono text-xs text-subtle transition hover:bg-ink hover:text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-info focus-visible:ring-offset-2">
                        ✕
                    </button>
                </div>

                <div class="space-y-5 px-5 py-5 sm:px-6">
                    {{-- Participants (roue classique / élimination) --}}
                    <template x-if="!openEntry.weights">
                        <div>
                            <p class="mb-2 font-mono text-[10px] uppercase tracking-widest text-faint">
                                Participants (<span x-text="openEntry.participants ? openEntry.participants.length : 0"></span>)
                            </p>
                            <div class="flex flex-wrap gap-1.5">
                                <template x-for="name in (openEntry.participants || [])" :key="name">
                                    <span class="inline-flex items-center gap-1 rounded-md border-2 border-ink px-2 py-1 font-mono text-[11px]" :class="name === openEntry.winner ? 'bg-secondary text-ink font-bold' : 'bg-wash text-ink'">
                                        <span x-show="name === openEntry.winner">🏆</span>
                                        <span x-text="name"></span>
                                    </span>
                                </template>
                            </div>
                        </div>
                    </template>

                    {{-- Participants + poids (roue pondérée), triés par poids décroissant, barre proportionnelle --}}
                    <template x-if="openEntry.weights">
                        <div>
                            <p class="mb-2 font-mono text-[10px] uppercase tracking-widest text-faint">
                                Participants et poids (<span x-text="openEntry.participants ? openEntry.participants.length : 0"></span>)
                            </p>
                            <div class="space-y-1.5">
                                <template x-for="name in (openEntry.participants || []).slice().sort((a, b) => (openEntry.weights[b] || 0) - (openEntry.weights[a] || 0))" :key="name">
                                    <div class="flex items-center gap-2">
                                        <span class="w-24 shrink-0 truncate font-mono text-[11px]" :class="name === openEntry.winner ? 'font-bold text-ink' : 'text-subtle'" x-text="(name === openEntry.winner ? '🏆 ' : '') + name"></span>
                                        <div class="h-2 flex-1 overflow-hidden rounded-full border border-ink/20 bg-wash">
                                            <div class="h-full rounded-full transition-all" :class="name === openEntry.winner ? 'bg-secondary' : 'bg-ink/25'" :style="'width:' + Math.max(6, (openEntry.weights[name] || 0) / Math.max(...Object.values(openEntry.weights)) * 100) + '%'"></div>
                                        </div>
                                        <span class="w-6 shrink-0 text-right font-mono text-[11px] text-subtle" x-text="openEntry.weights[name]"></span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>

                    {{-- Ordre des éliminations (roue d'élimination), sous forme de timeline --}}
                    <template x-if="openEntry.eliminations && openEntry.eliminations.length">
                        <div>
                            <p class="mb-2 font-mono text-[10px] uppercase tracking-widest text-faint">
                                Ordre des éliminations
                            </p>
                            <ol class="relative space-y-0 border-l-2 border-dashed border-line pl-4">
                                <template x-for="(name, i) in openEntry.eliminations" :key="i">
                                    <li class="relative pb-2.5">
                                        <span class="absolute -left-[21px] flex h-4 w-4 items-center justify-center rounded-full border-2 border-ink bg-wash font-mono text-[9px] text-subtle" x-text="i + 1"></span>
                                        <span class="font-mono text-[12px] text-ink" x-text="name"></span>
                                    </li>
                                </template>
                                <li class="relative">
                                    <span class="absolute -left-[21px] flex h-4 w-4 items-center justify-center rounded-full border-2 border-ink bg-secondary text-[9px]">🏆</span>
                                    <span class="font-mono text-[12px] font-bold text-ink" x-text="openEntry.winner"></span>
                                </li>
                            </ol>
                        </div>
                    </template>
                </div>
            </div>
        </template>
    </div>
</div>
