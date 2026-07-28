<div class="min-h-screen bg-[#14201C] text-[#F1ECE1] relative overflow-hidden antialiased pb-24 selection:bg-[#C79A56] selection:text-[#14201C]" style="font-family: 'Inter', sans-serif;">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=IBM+Plex+Mono:wght@500;600&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    {{-- Vignette décorative --}}
    <div class="absolute inset-0 pointer-events-none" aria-hidden="true" style="background: radial-gradient(ellipse at 50% -10%, rgba(199,154,86,0.08), transparent 60%);"></div>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 py-6 sm:py-10 space-y-5 sm:space-y-6 relative z-10">

        <div class="h-[3px] rounded-full" aria-hidden="true" style="background: linear-gradient(90deg, transparent, #C79A56, transparent);"></div>

        {{-- En-tête --}}
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 sm:gap-6 pt-2">
            <div>
                <a href="{{ route('home') }}" class="zinc-focusable inline-flex items-center gap-2 text-xs font-semibold tracking-widest uppercase text-[#8C9A94] hover:text-[#C79A56] transition">
                    ← Retour aux modes
                </a>
                <p class="text-[11px] font-semibold tracking-[0.25em] uppercase text-[#C79A56] mt-4">
                    Le classique du comptoir
                </p>
                <h1 class="text-5xl sm:text-6xl md:text-7xl leading-none tracking-wide text-[#F1ECE1] mt-1" style="font-family: 'Bebas Neue', sans-serif;">
                    4 · 2 · 1
                </h1>
                <p class="text-sm text-[#8C9A94] mt-3 max-w-sm">
                    Gardez les dés qui vous arrangent, relancez les autres, et visez la combinaison en {{ $this->maxThrows() }} lancer{{ $this->maxThrows() > 1 ? 's' : '' }} maximum.
                </p>
            </div>

            {{-- Statistiques locales --}}
            <div class="self-start md:self-auto flex gap-3">
                <div class="border border-[#F1ECE1]/15 rounded-sm px-6 py-4 text-center" style="background: repeating-linear-gradient(45deg, #1A2723, #1A2723 10px, #182420 10px, #182420 20px);">
                    <div class="text-[10px] font-semibold uppercase tracking-[0.2em] text-[#8C9A94]" id="games-count-label">Parties</div>
                    <div class="text-4xl text-[#D8DEDB] mt-1" style="font-family: 'IBM Plex Mono', monospace;" aria-labelledby="games-count-label">
                        {{ str_pad(count($history), 2, '0', STR_PAD_LEFT) }}
                    </div>
                </div>

                <div class="border border-[#C79A56]/30 rounded-sm px-6 py-4 text-center" style="background: repeating-linear-gradient(45deg, #1A2723, #1A2723 10px, #182420 10px, #182420 20px);">
                    <div class="text-[10px] font-semibold uppercase tracking-[0.2em] text-[#8C9A94]" id="win-count-label">Victoires</div>
                    <div class="text-4xl text-[#C79A56] mt-1" style="font-family: 'IBM Plex Mono', monospace;" aria-labelledby="win-count-label">
                        {{ str_pad($this->winCount(), 2, '0', STR_PAD_LEFT) }}
                    </div>
                </div>

            </div>
        </div>

        {{-- Erreurs système --}}
        @if($error)
        <div role="alert" class="rounded-sm bg-[#8B3A3A]/15 border border-[#8B3A3A]/40 px-4 py-3 text-[#E4B4B4] text-sm">
            <span aria-hidden="true">⚠</span> {{ $error }}
        </div>
        @endif

        {{-- Plateau de jeu --}}
        <div class="relative rounded-sm border border-[#C79A56]/20 p-6 sm:p-8 md:p-10 flex flex-col items-center overflow-hidden" style="background: linear-gradient(160deg, #46555A 0%, #3B484D 55%, #33403F 100%); box-shadow: inset 0 1px 0 rgba(255,255,255,0.08), 0 20px 40px -20px rgba(0,0,0,0.6);" wire:ignore x-data="diceGame(@js($dice), @js($kept), {{ $throwCount }}, {{ $isOver ? 'true' : 'false' }}, {{ $isWon ? 'true' : 'false' }}, @js($combinationLabel), {{ $this->maxThrows() }})" @dice-rolled.window="onDiceRolled($event.detail)" @dice-reset.window="onDiceReset()">

            <div class="absolute inset-x-0 top-0 h-16 pointer-events-none" aria-hidden="true" style="background: linear-gradient(180deg, rgba(255,255,255,0.08), transparent);"></div>

            {{-- Annonce d'accessibilité (A11y) --}}
            <div class="sr-only" role="status" aria-live="polite" x-text="announcement()"></div>

            {{-- Zone d'affichage des dés --}}
            <div class="flex gap-3 sm:gap-6 mb-2 relative" role="group" aria-label="Les trois dés">
                <template x-for="(value, index) in displayDice" :key="index">
                    <div class="flex flex-col items-center gap-2">
                        <button type="button" @click="toggleKeep(index)" :disabled="isRolling || isOver" :aria-pressed="keptState[index]" :aria-label="'Dé ' + (index + 1) + ', valeur ' + value + (keptState[index] ? ', gardé, appuyer pour relancer' : ', appuyer pour garder')" class="zinc-focusable zinc-motion relative w-14 h-14 sm:w-20 sm:h-20 rounded-[6px] transition-all duration-150 transform select-none" :class="{
                                    'ring-2 ring-[#C79A56] -translate-y-1': keptState[index],
                                    'cursor-not-allowed opacity-60': isOver,
                                    'cursor-pointer': !isOver && !isRolling,
                                    'rotate-6 scale-105': popped[index],
                                    'scale-95': spinning[index] && !popped[index]
                                }" style="background: linear-gradient(160deg, #F7F2E7, #E8DFC9); box-shadow: 0 6px 14px -4px rgba(0,0,0,0.5), inset 0 1px 0 rgba(255,255,255,0.6);">
                            <div class="absolute inset-1.5 sm:inset-2 grid grid-cols-3 grid-rows-3 place-items-center" aria-hidden="true">
                                <template x-for="n in 9" :key="n">
                                    <span class="w-1.5 h-1.5 sm:w-2.5 sm:h-2.5 rounded-full bg-[#1F2624] zinc-motion transition-opacity duration-100" :style="{ opacity: pipPositions(value).includes(n - 1) ? 1 : 0 }"></span>
                                </template>
                            </div>
                        </button>
                        <span class="text-[10px] font-semibold uppercase tracking-widest h-4" :class="keptState[index] ? 'text-[#C79A56]' : 'text-transparent'" aria-hidden="true">
                            gardé
                        </span>
                    </div>
                </template>
            </div>

            <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-[#D8DEDB]/60 mb-7">
                Touchez un dé pour le garder
            </p>

            {{-- État du lancer --}}
            <div class="text-center mb-7 min-h-[36px]">
                <div x-show="!isRolling">
                    <template x-if="isOver">
                        <div>
                            <template x-if="isWon">
                                <p class="text-2xl text-[#C79A56]" style="font-family: 'Bebas Neue', sans-serif; letter-spacing: 0.05em;">
                                    421 EN <span x-text="throwCount"></span> LANCER<span x-show="throwCount > 1">S</span> !
                                </p>
                            </template>
                            <template x-if="!isWon">
                                <p class="text-lg font-semibold text-[#E4B4B4]">
                                    Perdu — <span x-text="combinationLabel" class="lowercase"></span>
                                    <span class="block text-xs font-normal text-[#8C9A94] mt-1 normal-case" style="font-family: 'IBM Plex Mono', monospace;">
                                        en <span x-text="throwCount"></span> lancer<span x-show="throwCount > 1">s</span>
                                    </span>
                                </p>
                            </template>
                        </div>
                    </template>
                    <template x-if="!isOver">
                        <p class="text-sm text-[#D8DEDB]/80" style="font-family: 'IBM Plex Mono', monospace;">
                            LANCER <span x-text="throwCount"></span> / {{ $this->maxThrows() }}
                        </p>
                    </template>
                </div>
                <div x-show="isRolling" style="display: none;">
                    <p class="text-sm font-semibold text-[#C79A56] animate-pulse zinc-motion" style="font-family: 'IBM Plex Mono', monospace;">
                        lancement...
                    </p>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
                <button type="button" @click="startSpin()" x-show="!isOver || isRolling" :disabled="isRolling" class="zinc-focusable w-full sm:w-auto px-8 py-3 rounded-sm font-semibold text-sm uppercase tracking-wider transition disabled:opacity-50 disabled:cursor-wait text-[#14201C]" style="background: linear-gradient(160deg, #D9AF6E, #C79A56); box-shadow: 0 4px 12px -2px rgba(199,154,86,0.4);">
                    <span x-show="!isRolling">Lancer les dés</span>
                    <span x-show="isRolling" style="display: none;">Lancer...</span>
                </button>

                <button type="button" wire:click="resetGame" x-show="isOver && !isRolling" style="display: none;" class="zinc-focusable w-full sm:w-auto px-8 py-3 rounded-sm font-semibold text-sm uppercase tracking-wider text-[#F1ECE1] border border-[#F1ECE1]/25 hover:border-[#C79A56]/60 hover:text-[#C79A56] transition">
                    Nouvelle partie
                </button>
            </div>
        </div>

        {{-- Historique des parties --}}
        @if(count($history) > 0)
        <div class="rounded-sm border border-[#C79A56]/15 p-4 sm:p-5" style="background: #1A2723;">
            <h2 class="text-[10px] font-semibold uppercase tracking-[0.25em] text-[#C79A56] mb-3" id="history-heading">Ardoise des parties</h2>
            <div class="max-h-64 overflow-y-auto">
                <table class="w-full text-xs sm:text-sm" aria-labelledby="history-heading" style="font-family: 'IBM Plex Mono', monospace;">
                    <caption class="sr-only">Historique des parties de 421 jouées</caption>
                    <thead>
                        <tr class="sr-only">
                            <th scope="col">Dés</th>
                            <th scope="col">Combinaison</th>
                            <th scope="col">Lancers</th>
                            <th scope="col">Résultat</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#F1ECE1]/[0.06]">
                        @foreach(array_reverse($history) as $entry)
                        <tr>
                            <td class="font-semibold text-[#F1ECE1] tracking-widest py-2.5">{{ implode(' – ', $entry['dice']) }}</td>
                            <td class="text-[#8C9A94] text-xs py-2.5">{{ $entry['combination'] ?? '—' }}</td>
                            <td class="text-[#8C9A94] text-xs py-2.5">{{ $entry['throws'] }} lancer{{ $entry['throws'] > 1 ? 's' : '' }}</td>
                            <td class="{{ $entry['won'] ? 'text-[#C79A56]' : 'text-[#8C9A94]' }} font-semibold text-xs py-2.5 text-right">
                                <span aria-hidden="true">{{ $entry['won'] ? '✓' : '✗' }}</span> {{ $entry['won'] ? 'gagné' : 'perdu' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <div class="h-[3px] rounded-full" aria-hidden="true" style="background: linear-gradient(90deg, transparent, #C79A56, transparent);"></div>
    </div>
</div>
