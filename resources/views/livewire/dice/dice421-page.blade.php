<div class="min-h-screen bg-[#14201C] text-[#F1ECE1] relative overflow-hidden antialiased pb-24 selection:bg-[#C79A56] selection:text-[#14201C]" style="font-family: 'Inter', sans-serif;">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=IBM+Plex+Mono:wght@500;600&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        /* Focus clavier visible et cohérent sur tous les contrôles interactifs de ce composant */
        .zinc-focusable:focus-visible {
            outline: 2px solid #C79A56;
            outline-offset: 3px;
            border-radius: 2px;
        }

        @media (prefers-reduced-motion: reduce) {
            .zinc-motion {
                transition: none !important;
                animation: none !important;
                transform: none !important;
            }
        }

    </style>

    {{-- AMBIANCE : vignette décorative, ignorée par les lecteurs d'écran --}}
    <div class="absolute inset-0 pointer-events-none" aria-hidden="true" style="background: radial-gradient(ellipse at 50% -10%, rgba(199,154,86,0.08), transparent 60%);"></div>

    <div class="max-w-3xl mx-auto px-6 py-10 space-y-6 relative z-10">

        <div class="h-[3px] rounded-full" aria-hidden="true" style="background: linear-gradient(90deg, transparent, #C79A56, transparent);"></div>

        {{-- HEADER --}}
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-6 pt-2">
            <div>
                <a href="{{ route('home') }}" class="zinc-focusable inline-flex items-center gap-2 text-xs font-semibold tracking-widest uppercase text-[#8C9A94] hover:text-[#C79A56] transition">
                    ← Retour aux modes
                </a>
                <p class="text-[11px] font-semibold tracking-[0.25em] uppercase text-[#C79A56] mt-4">
                    Le classique du comptoir
                </p>
                <h1 class="text-7xl leading-none tracking-wide text-[#F1ECE1] mt-1" style="font-family: 'Bebas Neue', sans-serif;">
                    4 · 2 · 1
                </h1>
                <p class="text-sm text-[#8C9A94] mt-3 max-w-sm">
                    Gardez les dés qui vous arrangent, relancez les autres, et visez la combinaison en {{ $this->maxThrows() }} lancer{{ $this->maxThrows() > 1 ? 's' : '' }} maximum.
                </p>
            </div>

            {{-- TALLY --}}
            <div class="border border-[#C79A56]/30 rounded-sm px-6 py-4 text-center" style="background: repeating-linear-gradient(45deg, #1A2723, #1A2723 10px, #182420 10px, #182420 20px);">
                <div class="text-[10px] font-semibold uppercase tracking-[0.2em] text-[#8C9A94]" id="win-count-label">Victoires</div>
                <div class="text-4xl text-[#C79A56] mt-1" style="font-family: 'IBM Plex Mono', monospace;" aria-labelledby="win-count-label">
                    {{ str_pad($this->winCount(), 2, '0', STR_PAD_LEFT) }}
                </div>
            </div>
        </div>

        {{-- ALERTES ERREURS --}}
        @if($error)
        <div role="alert" class="rounded-sm bg-[#8B3A3A]/15 border border-[#8B3A3A]/40 px-4 py-3 text-[#E4B4B4] text-sm">
            <span aria-hidden="true">⚠</span> {{ $error }}
        </div>
        @endif

        {{-- PLATEAU / COMPTOIR ZINC --}}
        <div class="relative rounded-sm border border-[#C79A56]/20 p-10 flex flex-col items-center overflow-hidden" style="background: linear-gradient(160deg, #46555A 0%, #3B484D 55%, #33403F 100%); box-shadow: inset 0 1px 0 rgba(255,255,255,0.08), 0 20px 40px -20px rgba(0,0,0,0.6);" x-data="diceGame(@js($dice), @js($kept), {{ $throwCount }}, {{ $isOver ? 'true' : 'false' }}, {{ $isWon ? 'true' : 'false' }}, @js($combinationLabel), {{ $this->maxThrows() }})" @dice-rolled.window="onDiceRolled($event.detail)" @dice-reset.window="onDiceReset()">

            <div class="absolute inset-x-0 top-0 h-16 pointer-events-none" aria-hidden="true" style="background: linear-gradient(180deg, rgba(255,255,255,0.08), transparent);"></div>

            {{-- Annonce live pour lecteurs d'écran : seule source d'info sur l'état de la partie
                 pour les technologies d'assistance (le rendu visuel des dés étant purement décoratif à leurs yeux) --}}
            <div class="sr-only" role="status" aria-live="polite" x-text="announcement()"></div>

            {{-- LES DÉS --}}
            <div class="flex gap-6 mb-7 relative" wire:ignore role="group" aria-label="Les trois dés">
                <template x-for="(value, index) in displayDice" :key="index">
                    <button type="button" @click="toggleKeep(index)" :disabled="isRolling || isOver" :aria-pressed="keptState[index]" :aria-label="'Dé ' + (index + 1) + ', valeur ' + value + (keptState[index] ? ', gardé, appuyer pour relancer' : ', appuyer pour garder')" class="zinc-focusable zinc-motion relative w-20 h-20 rounded-[6px] transition-all duration-150 transform select-none" :class="{
                                'ring-2 ring-[#C79A56] -translate-y-1': keptState[index],
                                'cursor-not-allowed opacity-60': isOver,
                                'cursor-pointer': !isOver && !isRolling,
                                'rotate-6 scale-105': popped[index],
                                'scale-95': spinning[index] && !popped[index]
                            }" style="background: linear-gradient(160deg, #F7F2E7, #E8DFC9); box-shadow: 0 6px 14px -4px rgba(0,0,0,0.5), inset 0 1px 0 rgba(255,255,255,0.6);">
                        <div class="absolute inset-2 grid grid-cols-3 grid-rows-3 place-items-center" aria-hidden="true">
                            <template x-for="n in 9" :key="n">
                                <span class="w-2.5 h-2.5 rounded-full bg-[#1F2624] zinc-motion transition-opacity duration-100" :style="{ opacity: pipPositions(value).includes(n - 1) ? 1 : 0 }"></span>
                            </template>
                        </div>
                        <div class="absolute -top-2 -right-1 text-[9px] font-semibold uppercase tracking-wider text-[#C79A56]" x-show="keptState[index]" aria-hidden="true" style="display: none;">gardé</div>
                    </button>
                </template>
            </div>

            <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-[#D8DEDB]/60 mb-7">
                Touchez un dé pour le garder
            </p>

            {{-- ZONE DE RÉSULTAT --}}
            <div class="text-center mb-7 min-h-[64px]" aria-hidden="true">
                <div x-show="!isRolling">
                    <template x-if="isOver">
                        <div x-show="isOver" x-transition:enter="zinc-motion transition duration-300 ease-out" x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100">
                            <template x-if="isWon">
                                <div class="inline-flex flex-col items-center gap-1 px-6 py-3 rounded-sm border" style="border-color: rgba(199,154,86,0.5); background: linear-gradient(160deg, rgba(199,154,86,0.12), rgba(199,154,86,0.03)); box-shadow: 0 0 24px -4px rgba(199,154,86,0.35);">
                                    <span class="text-[10px] font-semibold uppercase tracking-[0.25em] text-[#C79A56]">Victoire</span>
                                    <p class="text-3xl text-[#F1ECE1]" style="font-family: 'Bebas Neue', sans-serif; letter-spacing: 0.06em;">
                                        421 EN <span x-text="throwCount"></span> LANCER<span x-show="throwCount > 1">S</span> !
                                    </p>
                                </div>
                            </template>
                            <template x-if="!isWon">
                                <div class="inline-flex flex-col items-center gap-1 px-6 py-3 rounded-sm border" style="border-color: rgba(139,58,58,0.4); background: rgba(139,58,58,0.08);">
                                    <span class="text-[10px] font-semibold uppercase tracking-[0.25em] text-[#E4B4B4]/70">Défaite</span>
                                    <p class="text-lg font-semibold text-[#E4B4B4]" style="font-family: 'IBM Plex Mono', monospace;">
                                        <span x-text="combinationLabel"></span>
                                    </p>
                                </div>
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

            {{-- BOUTONS --}}
            <div class="flex gap-3">
                <button type="button" @click="startSpin()" x-show="!isOver || isRolling" :disabled="isRolling" class="zinc-focusable px-8 py-3 rounded-sm font-semibold text-sm uppercase tracking-wider transition disabled:opacity-50 disabled:cursor-wait text-[#14201C]" style="background: linear-gradient(160deg, #D9AF6E, #C79A56); box-shadow: 0 4px 12px -2px rgba(199,154,86,0.4);">
                    <span x-show="!isRolling">Lancer les dés</span>
                    <span x-show="isRolling" style="display: none;">Lancer...</span>
                </button>

                <button type="button" wire:click="resetGame" x-show="isOver && !isRolling" style="display: none;" class="zinc-focusable px-8 py-3 rounded-sm font-semibold text-sm uppercase tracking-wider text-[#F1ECE1] border border-[#F1ECE1]/25 hover:border-[#C79A56]/60 hover:text-[#C79A56] transition">
                    Nouvelle partie
                </button>
            </div>
        </div>

        {{--
            @script/@endscript est OBLIGATOIRE : composant class-based
            (Dice421Page.php séparé de cette vue) sous Livewire 4.
        --}}
        @script
        <script>
            Alpine.data('diceGame', (initialDice, initialKept, initialThrows, initialIsOver, initialIsWon, initialLabel, maxThrows) => ({
                isRolling: false
                , isOver: initialIsOver
                , isWon: initialIsWon
                , combinationLabel: initialLabel
                , throwCount: initialThrows
                , maxThrows: maxThrows
                , displayDice: [...initialDice]
                , keptState: [...initialKept]
                , popped: [false, false, false]
                , spinning: [false, false, false]
                , pendingServerData: null
                , pendingCount: 0
                , awaitingImmediateResolve: false
                , reducedMotion: window.matchMedia('(prefers-reduced-motion: reduce)').matches,

                // Positions de points actives (index 0-8 dans une grille 3x3) par valeur de dé.
                pipPositions(value) {
                    return {
                        1: [4]
                        , 2: [0, 8]
                        , 3: [0, 4, 8]
                        , 4: [0, 2, 6, 8]
                        , 5: [0, 2, 4, 6, 8]
                        , 6: [0, 2, 3, 5, 6, 8]
                    , } [value] || [];
                },

                // Texte annoncé aux lecteurs d'écran via la région aria-live sr-only ;
                // seule source d'info fiable côté audio, le plateau visuel étant décoratif pour eux.
                announcement() {
                    if (this.isRolling) {
                        return 'Lancement des dés en cours.';
                    }
                    if (this.isOver) {
                        return this.isWon ?
                            `Combinaison 421 obtenue en ${this.throwCount} lancer${this.throwCount > 1 ? 's' : ''}. Partie gagnée.` :
                            `Partie perdue. Combinaison finale : ${this.combinationLabel}.`;
                    }
                    return `Lancer ${this.throwCount} sur ${this.maxThrows}.`;
                },

                toggleKeep(index) {
                    if (this.isRolling || this.isOver) return;
                    this.$wire.toggleKeep(index);
                    this.keptState[index] = !this.keptState[index];
                },

                startSpin() {
                    if (this.isRolling || this.isOver) return;
                    this.isRolling = true;
                    this.pendingServerData = null;
                    this.pendingCount = 0;

                    this.$wire.roll();

                    // Sous prefers-reduced-motion, on saute l'animation de rotation des dés :
                    // la valeur finale s'affiche directement dès la réponse serveur.
                    if (this.reducedMotion) {
                        this.awaitingImmediateResolve = true;
                        return;
                    }

                    this.displayDice.forEach((_, i) => {
                        if (this.keptState[i]) return;
                        this.pendingCount++;
                        this.spinning[i] = true;
                        this.spinDie(i, performance.now(), 500 + i * 350 + Math.floor(Math.random() * 300));
                    });

                    // Cas limite : les 3 dés sont gardés, rien à animer.
                    if (this.pendingCount === 0) {
                        this.awaitingImmediateResolve = true;
                    }
                },

                // Ralentissement progressif (ease-out cubique) : ~40ms entre deux valeurs au
                // départ, jusqu'à ~220ms en fin de course, pour un effet de dé qui cale.
                spinDie(index, startTime, duration) {
                    if (!this.spinning[index]) return;

                    this.displayDice[index] = Math.floor(Math.random() * 6) + 1;

                    const elapsed = performance.now() - startTime;
                    const progress = Math.min(elapsed / duration, 1);
                    const nextDelay = 40 + Math.pow(progress, 3) * 180;

                    setTimeout(() => this.spinDie(index, startTime, duration), nextDelay);
                },

                onDiceRolled(detail) {
                    this.pendingServerData = detail;
                    const finalDice = this.pendingServerData.dice;

                    if (this.reducedMotion || this.awaitingImmediateResolve) {
                        this.displayDice = [...finalDice];
                        this.awaitingImmediateResolve = false;
                        this.finalizeRoll();
                        return;
                    }

                    const settle = (i, finalVal) => {
                        this.spinning[i] = false;
                        this.displayDice[i] = finalVal;
                        this.popped[i] = true;
                        setTimeout(() => this.popped[i] = false, 180);
                        this.pendingCount--;
                        if (this.pendingCount <= 0) this.finalizeRoll();
                    };

                    finalDice.forEach((finalVal, i) => {
                        if (this.keptState[i]) {
                            this.displayDice[i] = finalVal;
                            return;
                        }
                        const randomDelay = Math.floor(Math.random() * 300) + (i * 350) + 400;
                        setTimeout(() => settle(i, finalVal), randomDelay);
                    });
                },

                finalizeRoll() {
                    // Fix : ces champs sont désormais bien présents dans le
                    // payload dispatché par roll() côté serveur (dice,
                    // throwCount, isOver, isWon, combinationLabel).
                    // Auparavant seul `dice` était transmis, donc isOver
                    // restait undefined et le bouton "Lancer" ne
                    // disparaissait jamais après la fin de partie.
                    this.throwCount = this.pendingServerData.throwCount;
                    this.isOver = this.pendingServerData.isOver;
                    this.isWon = this.pendingServerData.isWon;
                    this.combinationLabel = this.pendingServerData.combinationLabel;
                    this.isRolling = false;
                },

                onDiceReset() {
                    this.isRolling = false;
                    this.isOver = false;
                    this.isWon = false;
                    this.combinationLabel = null;
                    this.throwCount = 0;
                    this.keptState = [false, false, false];
                    this.displayDice = [1, 1, 1];
                    this.spinning = [false, false, false];
                    this.pendingCount = 0;
                }
            }));

        </script>
        @endscript

        {{-- HISTORIQUE — tableau sémantique --}}
        @if(count($history) > 0)
        <div class="rounded-sm border border-[#C79A56]/15 p-5" style="background: #1A2723;">
            <h2 class="text-[10px] font-semibold uppercase tracking-[0.25em] text-[#C79A56] mb-3" id="history-heading">Ardoise des parties</h2>
            <div class="max-h-64 overflow-y-auto">
                <table class="w-full text-sm" aria-labelledby="history-heading" style="font-family: 'IBM Plex Mono', monospace;">
                    <caption class="sr-only">Historique des parties de 421 jouées, dés obtenus, combinaison, nombre de lancers et résultat</caption>
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
                            <td class="text-[#8C9A94] text-xs py-2.5 normal-case">{{ $entry['combination'] ?? '—' }}</td>
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
