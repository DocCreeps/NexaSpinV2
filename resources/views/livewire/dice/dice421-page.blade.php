<div class="min-h-screen w-full bg-surface text-ink antialiased selection:bg-secondary selection:text-ink">
    <div class="mx-auto max-w-3xl space-y-6 px-4 py-6 sm:space-y-8 sm:px-10 sm:py-10">

        {{-- Header --}}
        <header class="flex flex-col gap-4 border-b-4 border-ink pb-5 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 rounded text-sm font-semibold text-muted transition-colors hover:text-ink focus:outline-none focus-visible:ring-2 focus-visible:ring-info focus-visible:ring-offset-2">
                    ← Salle
                </a>

                <p class="mt-4 font-mono text-[10px] uppercase tracking-widest text-faint">
                    ◆ Le classique du comptoir ◆
                </p>

                <h1 class="mt-1 font-display text-4xl leading-none text-ink sm:text-5xl">
                    4 · 2 · 1
                </h1>

                <p class="mt-3 max-w-md text-sm text-muted">
                    Gardez les dés qui vous arrangent, relancez les autres, visez la combinaison en
                    {{ $this->maxThrows() }} lancer{{ $this->maxThrows() > 1 ? 's' : '' }} max.
                </p>
            </div>

            <div class="flex gap-3 self-start">
                <div class="card-hard min-w-[72px] rounded-xl border-2 border-ink bg-panel px-4 py-3 text-center">
                    <p class="font-mono text-[9px] uppercase tracking-widest text-subtle">Parties</p>
                    <p class="mt-0.5 font-display text-2xl text-ink">
                        {{ str_pad(count($history), 2, '0', STR_PAD_LEFT) }}
                    </p>
                </div>

                <div class="card-hard min-w-[72px] rounded-xl border-2 border-ink bg-secondary px-4 py-3 text-center">
                    <p class="font-mono text-[9px] uppercase tracking-widest text-ink/70">Victoires</p>
                    <p class="mt-0.5 font-display text-2xl text-ink">
                        {{ str_pad($this->winCount(), 2, '0', STR_PAD_LEFT) }}
                    </p>
                </div>
            </div>
        </header>

        {{-- Erreur --}}
        @if($error)
        <div role="alert" class="rounded-xl border-2 border-ink bg-danger/10 px-4 py-3 text-sm font-semibold text-danger">
            ⚠ {{ $error }}
        </div>
        @endif

        {{-- Plateau --}}
        <section class="card-hard relative flex flex-col items-center overflow-hidden rounded-2xl border-2 border-ink bg-panel p-6 sm:p-8 md:p-10" wire:ignore x-data="diceGame(@js($dice), @js($kept), {{ $throwCount }}, {{ $isOver ? 'true' : 'false' }}, {{ $isWon ? 'true' : 'false' }}, @js($combinationLabel), {{ $this->maxThrows() }})" @dice-rolled.window="onDiceRolled($event.detail)" @dice-reset.window="onDiceReset()">
            <div class="sr-only" role="status" aria-live="polite" x-text="announcement()"></div>

            {{-- Dés --}}
            <div class="mb-2 flex gap-3 sm:gap-5" role="group" aria-label="Les trois dés">
                <template x-for="(value, index) in displayDice" :key="index">
                    <div class="flex flex-col items-center gap-2">
                        <button type="button" @click="toggleKeep(index)" :disabled="isRolling || isOver" :aria-pressed="keptState[index]" :aria-label="'Dé ' + (index + 1) + ', valeur ' + value + (keptState[index] ? ', gardé, appuyer pour relancer' : ', appuyer pour garder')" class="relative h-16 w-16 select-none rounded-xl border-2 border-ink bg-panel shadow-hard transition focus:outline-none focus-visible:ring-2 focus-visible:ring-info focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60 sm:h-20 sm:w-20" :class="{
                                'tile-selected': keptState[index],
                                'cursor-pointer': !isOver && !isRolling,
                                'rotate-6 scale-105': popped[index],
                                'scale-95': spinning[index] && !popped[index]
                            }">
                            <div class="absolute inset-2 grid grid-cols-3 grid-rows-3 place-items-center" aria-hidden="true">
                                <template x-for="n in 9" :key="n">
                                    <span class="h-2 w-2 rounded-full bg-ink transition-opacity duration-100 sm:h-2.5 sm:w-2.5" :style="{ opacity: pipPositions(value).includes(n - 1) ? 1 : 0 }"></span>
                                </template>
                            </div>
                        </button>

                        <span class="h-4 font-mono text-[9px] uppercase tracking-widest" :class="keptState[index] ? 'text-primary' : 'text-transparent'" aria-hidden="true">
                            KEEP
                        </span>
                    </div>
                </template>
            </div>

            <p class="mb-6 font-mono text-[10px] uppercase tracking-widest text-faint">
                Touchez un dé pour le garder
            </p>

            {{-- État --}}
            <div class="mb-7 min-h-[44px] text-center">
                <div x-show="!isRolling">
                    <template x-if="isOver">
                        <div>
                            <template x-if="isWon">
                                <p class="font-display text-2xl text-primary sm:text-3xl">
                                    421 EN <span x-text="throwCount"></span> LANCER<span x-show="throwCount > 1">S</span> !
                                </p>
                            </template>
                            <template x-if="!isWon">
                                <div>
                                    <p class="font-display text-xl text-ink">
                                        Perdu — <span x-text="combinationLabel"></span>
                                    </p>
                                    <p class="mt-1 font-mono text-xs text-subtle">
                                        en <span x-text="throwCount"></span> lancer<span x-show="throwCount > 1">s</span>
                                    </p>
                                </div>
                            </template>
                        </div>
                    </template>

                    <template x-if="!isOver">
                        <p class="font-mono text-sm tracking-wider text-muted">
                            LANCER <span x-text="throwCount"></span> / {{ $this->maxThrows() }}
                        </p>
                    </template>
                </div>

                <div x-show="isRolling" style="display: none;">
                    <p class="animate-pulse font-mono text-sm tracking-wider text-primary">
                        LANCEMENT...
                    </p>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex w-full flex-col gap-3 sm:w-auto sm:flex-row">
                <button type="button" @click="startSpin()" x-show="!isOver || isRolling" :disabled="isRolling" class="btn-press inline-flex w-full items-center justify-center gap-2 rounded-xl border-2 border-ink bg-primary px-8 py-3.5 font-display text-xs text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-info focus-visible:ring-offset-2 sm:w-auto sm:text-sm">
                    <span x-show="!isRolling">▶ LANCER LES DÉS</span>
                    <span x-show="isRolling" style="display: none;">LANCEMENT...</span>
                </button>

                <button type="button" wire:click="resetGame" x-show="isOver && !isRolling" style="display: none;" class="card-hard inline-flex w-full items-center justify-center rounded-xl border-2 border-ink bg-panel px-8 py-3.5 font-display text-xs text-ink transition hover:-translate-x-px hover:-translate-y-px hover:shadow-hard-lg focus:outline-none focus-visible:ring-2 focus-visible:ring-info focus-visible:ring-offset-2 sm:w-auto sm:text-sm">
                    NOUVELLE PARTIE
                </button>
            </div>
        </section>

        {{-- Historique --}}
        @if(count($history) > 0)
        <section class="card-hard rounded-2xl border-2 border-ink bg-panel p-4 sm:p-5">
            <div class="mb-3 flex items-center justify-between gap-3">
                <h2 id="history-heading" class="font-mono text-[10px] uppercase tracking-widest text-faint">
                    Ardoise des parties
                </h2>
                <div class="flex items-center gap-3">
                    <a href="{{ route('history') }}?filter=dice_421" class="font-mono text-[10px] uppercase tracking-widest text-info hover:underline">
                        Historique complet →
                    </a>
                    <button type="button" wire:click="clearHistory" wire:confirm="Vider l'historique de ce mode ?" class="font-mono text-[10px] uppercase tracking-widest text-subtle transition hover:text-danger">
                        Vider
                    </button>
                </div>
            </div>

            <div class="max-h-64 space-y-1.5 overflow-y-auto pr-1">
                @foreach(array_reverse($history) as $entry)
                <div @class([
                    'flex items-center gap-3 rounded-xl border-2 px-3 py-2.5',
                    'border-ink bg-secondary/20' => $entry['won'],
                    'border-line bg-wash' => ! $entry['won'],
                    ])>
                    <span @class([
                        'shrink-0 rounded-md border px-2 py-1 font-mono text-[10px] font-bold uppercase',
                        'border-ink bg-secondary text-ink' => $entry['won'],
                        'border-line bg-panel text-subtle' => ! $entry['won'],
                        ])>
                        {{ $entry['won'] ? '✓' : '✗' }}
                    </span>

                    <div class="min-w-0 flex-1">
                        <div class="flex items-baseline gap-2">
                            <span class="font-display text-sm tracking-wide text-ink">
                                {{ $entry['combination'] ?? 'Aucune combinaison' }}
                            </span>
                        </div>
                        <p class="mt-0.5 font-mono text-[11px] text-subtle">
                            {{ implode(' · ', $entry['dice']) }}
                            <span class="text-faint">— {{ $entry['throws'] }} lancer{{ $entry['throws'] > 1 ? 's' : '' }}</span>
                        </p>
                    </div>
                </div>
                @endforeach
            </div>
        </section>
        @endif
    </div>
</div>
