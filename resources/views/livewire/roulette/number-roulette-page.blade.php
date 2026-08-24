<div class="min-h-screen w-full bg-surface text-ink antialiased selection:bg-secondary selection:text-ink" x-data="{
        spinning: false,
        revealed: null,
        finished: false,
        cycleTimer: null
    }" x-on:roulette-spin.window="
        spinning = true;
        finished = false;
        revealed = null;
        const pockets = ['0', '00', ...Array.from({ length: 36 }, (_, i) => String(i + 1))];
        clearInterval(cycleTimer);
        cycleTimer = setInterval(() => { revealed = pockets[Math.floor(Math.random() * pockets.length)]; }, 70);
        setTimeout(() => {
            clearInterval(cycleTimer);
            revealed = $event.detail.result;
            spinning = false;
            finished = true;
            setTimeout(() => $wire.confirmSpin(), 500);
        }, 1500);
    ">
    <div class="mx-auto max-w-6xl space-y-6 px-4 py-6 sm:space-y-8 sm:px-10 sm:py-10">

        {{-- HEADER --}}
        <header class="flex flex-col gap-4 border-b-4 border-ink pb-5 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 rounded text-sm font-semibold text-muted transition-colors hover:text-ink focus:outline-none focus-visible:ring-2 focus-visible:ring-info focus-visible:ring-offset-2">
                    ← Salle
                </a>

                <p class="mt-4 font-mono text-[10px] uppercase tracking-widest text-faint">
                    ◆ Roulette américaine ◆
                </p>

                <h1 class="mt-1 font-display text-4xl leading-none text-ink sm:text-5xl">
                    Roulette numérique
                </h1>

                <p class="mt-3 max-w-md text-sm text-muted">
                    Misez sur un numéro, une couleur, une douzaine ou une colonne. Votre cagnotte est conservée entre vos visites (1 mois).
                </p>
            </div>

            <div class="card-hard self-start rounded-xl border-2 border-ink bg-panel px-5 py-3 text-center min-w-[130px]">
                <p class="font-mono text-[9px] uppercase tracking-widest text-subtle">Cagnotte</p>
                <p class="mt-0.5 font-display text-2xl text-ink">
                    {{ number_format($bankroll, 0, ',', ' ') }}
                </p>
                <button type="button" wire:click="resetBankroll" wire:confirm="Réinitialiser la cagnotte à {{ number_format($startingBankroll, 0, ',', ' ') }} ?" class="mt-1 font-mono text-[9px] uppercase tracking-widest text-subtle underline decoration-dotted hover:text-danger">
                    Réinitialiser
                </button>
            </div>
        </header>

        {{-- ERREUR --}}
        @if($error)
        <div role="alert" class="rounded-xl border-2 border-ink bg-danger/10 px-4 py-3 text-sm font-semibold text-danger">
            ⚠ {{ $error }}
        </div>
        @endif

        {{-- GRILLE --}}
        <div class="grid items-start gap-6 lg:grid-cols-12 lg:gap-8">

            {{-- TABLE DE JEU --}}
            <div class="space-y-6 lg:col-span-7">
                <section class="card-hard rounded-2xl border-2 border-ink bg-panel p-5 sm:p-6">

                    {{-- RÉSULTAT --}}
                    <div class="mb-6 flex flex-col items-center justify-center gap-3">
                        <div class="flex h-24 w-24 items-center justify-center rounded-full border-4 border-ink font-display text-3xl shadow-hard transition-all"
                            x-bind:class="(spinning ? 'animate-pulse ' : '') + (revealed === null ? 'bg-wash text-faint' : (revealed === '0' || revealed === '00' ? 'bg-emerald-600 text-white' : ({{ json_encode(collect(\App\Domain\Roulette\RoulettePocket::all())->mapWithKeys(fn($p) => [$p => \App\Domain\Roulette\RoulettePocket::color($p)])) }}[revealed] === 'red' ? 'bg-red-600 text-white' : 'bg-ink text-white')))"
                            x-text="revealed ?? '?'">
                        </div>

                        <div x-show="finished && ! spinning" x-cloak @class([
                            'rounded-xl border-2 border-ink px-5 py-2 font-display text-base shadow-hard',
                            ])
                            :class="{{ $lastWin ? "'bg-secondary text-ink'" : "'bg-danger/10 text-danger'" }}">
                            @if($lastWin !== null)
                            {{ $lastWin ? '✓ Gagné' : '✗ Perdu' }}
                            @if($lastPayout !== null)
                            ({{ $lastPayout > 0 ? '+' : '' }}{{ number_format($lastPayout, 0, ',', ' ') }})
                            @endif
                            @endif
                        </div>
                    </div>

                    {{-- 0 / 00 --}}
                    <div class="mb-1.5 flex gap-1.5">
                        @foreach(['0', '00'] as $zero)
                        <button type="button" wire:click="selectNumber('{{ $zero }}')" @disabled($spinning) @class([
                            'flex-1 rounded-lg border-2 border-ink py-2.5 font-mono text-sm font-bold transition',
                            'bg-emerald-600 text-white shadow-hard' => $betType === 'straight' && $betNumber === $zero,
                            'bg-emerald-600/80 text-white hover:bg-emerald-600' => ! ($betType === 'straight' && $betNumber === $zero),
                            ])>
                            {{ $zero }}
                        </button>
                        @endforeach
                    </div>

                    {{-- GRILLE 1-36 (3 lignes x 12 colonnes, disposition casino) --}}
                    <div class="space-y-1.5">
                        @for($row = 3; $row >= 1; $row--)
                        <div class="grid grid-cols-12 gap-1.5">
                            @for($col = 1; $col <= 12; $col++)
                            @php
                            $number = (string) (($col - 1) * 3 + $row);
                            $color = \App\Domain\Roulette\RoulettePocket::color($number);
                            $selected = $betType === 'straight' && $betNumber === $number;
                            @endphp
                            <button type="button" wire:click="selectNumber('{{ $number }}')" @disabled($spinning) @class([
                                'rounded-md border-2 border-ink py-2 font-mono text-[11px] font-bold transition sm:text-xs',
                                'ring-2 ring-info ring-offset-1' => $selected,
                                'bg-red-600 text-white hover:bg-red-500' => $color === 'red',
                                'bg-ink text-white hover:bg-ink/80' => $color === 'black',
                                ])>
                                {{ $number }}
                            </button>
                            @endfor
                        </div>
                        @endfor
                    </div>

                    {{-- CHANCES SIMPLES --}}
                    <div class="mt-3 grid grid-cols-3 gap-1.5 sm:grid-cols-6">
                        @foreach($this->simpleChances() as $bet)
                        <button type="button" wire:click="selectBetType('{{ $bet->value }}')" @disabled($spinning) @class([
                            'rounded-lg border-2 border-ink px-2 py-2 font-mono text-[10px] uppercase tracking-wide transition',
                            'bg-ink text-white shadow-hard' => $betType === $bet->value,
                            'bg-wash text-muted hover:text-ink' => $betType !== $bet->value,
                            ])>
                            {{ $bet->label() }}
                        </button>
                        @endforeach
                    </div>

                    {{-- DOUZAINES / COLONNES --}}
                    <div class="mt-1.5 grid grid-cols-3 gap-1.5">
                        @foreach($this->dozenBets() as $bet)
                        <button type="button" wire:click="selectBetType('{{ $bet->value }}')" @disabled($spinning) @class([
                            'rounded-lg border-2 border-ink px-2 py-2 font-mono text-[10px] uppercase tracking-wide transition',
                            'bg-ink text-white shadow-hard' => $betType === $bet->value,
                            'bg-wash text-muted hover:text-ink' => $betType !== $bet->value,
                            ])>
                            {{ $bet->label() }}
                        </button>
                        @endforeach
                    </div>
                    <div class="mt-1.5 grid grid-cols-3 gap-1.5">
                        @foreach($this->columnBets() as $bet)
                        <button type="button" wire:click="selectBetType('{{ $bet->value }}')" @disabled($spinning) @class([
                            'rounded-lg border-2 border-ink px-2 py-2 font-mono text-[10px] uppercase tracking-wide transition',
                            'bg-ink text-white shadow-hard' => $betType === $bet->value,
                            'bg-wash text-muted hover:text-ink' => $betType !== $bet->value,
                            ])>
                            {{ $bet->label() }}
                        </button>
                        @endforeach
                    </div>
                </section>
            </div>

            {{-- MISE & CONTRÔLES --}}
            <div class="space-y-6 lg:col-span-5">
                <section class="card-hard rounded-2xl border-2 border-ink bg-panel p-5">
                    <h2 class="mb-4 font-display text-lg text-ink">Votre mise</h2>

                    <div class="mb-4 rounded-xl border-2 border-ink bg-wash px-4 py-3">
                        <p class="font-mono text-[10px] uppercase tracking-widest text-subtle">Pari actuel</p>
                        <p class="mt-1 font-display text-base text-ink">
                            {{ \App\Domain\Roulette\Enums\RouletteBetType::from($betType)->label() }}
                            @if($betType === 'straight' && $betNumber !== null)
                            <span class="font-mono text-sm text-subtle">— {{ $betNumber }}</span>
                            @endif
                        </p>
                        <p class="mt-1 font-mono text-[10px] text-faint">
                            Gain : x{{ \App\Domain\Roulette\Enums\RouletteBetType::from($betType)->payoutMultiplier() }} la mise
                        </p>
                    </div>

                    <label for="stake-input" class="mb-1.5 block font-mono text-[10px] uppercase tracking-widest text-subtle">
                        Montant misé
                    </label>
                    <div class="flex items-center gap-2">
                        <button type="button" wire:click="$set('stake', {{ max($stake - 10, 1) }})" @disabled($spinning) class="h-10 w-10 shrink-0 rounded-lg border-2 border-ink bg-wash font-bold text-ink transition hover:bg-panel disabled:opacity-40">−</button>

                        <input type="number" id="stake-input" wire:model="stake" min="1" max="{{ $bankroll }}" @disabled($spinning) class="w-full rounded-xl border-2 border-ink bg-panel px-4 py-2.5 text-center text-sm font-bold text-ink focus:outline-none focus:ring-2 focus:ring-info focus:ring-offset-2 [appearance:textfield] [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none">

                        <button type="button" wire:click="$set('stake', {{ min($stake + 10, max($bankroll, 1)) }})" @disabled($spinning) class="h-10 w-10 shrink-0 rounded-lg border-2 border-ink bg-wash font-bold text-ink transition hover:bg-panel disabled:opacity-40">+</button>
                    </div>

                    <div class="mt-2 flex gap-1.5">
                        @foreach([25, 50, 100, 250] as $amount)
                        <button type="button" wire:click="$set('stake', {{ $amount }})" @disabled($spinning) class="flex-1 rounded-md border-2 border-ink bg-wash py-1.5 font-mono text-[10px] text-muted transition hover:text-ink disabled:opacity-40">
                            {{ $amount }}
                        </button>
                        @endforeach
                        <button type="button" wire:click="$set('stake', {{ $bankroll }})" @disabled($spinning || $bankroll < 1) class="flex-1 rounded-md border-2 border-ink bg-wash py-1.5 font-mono text-[10px] text-muted transition hover:text-ink disabled:opacity-40">
                            Tout
                        </button>
                    </div>
                </section>

                <button type="button" wire:click="spin" wire:loading.attr="disabled" wire:target="spin" x-bind:disabled="spinning || {{ $this->canSpin() ? 'false' : 'true' }}" @disabled(! $this->canSpin())
                    class="btn-press w-full rounded-xl border-2 border-ink bg-primary py-4 font-display text-sm text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-info focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50"
                    >
                    <span x-show="!spinning">
                        @if($stake > $bankroll)
                        CAGNOTTE INSUFFISANTE
                        @else
                        ▶ LANCER LA ROULETTE
                        @endif
                    </span>
                    <span x-show="spinning" x-cloak>LA BILLE TOURNE...</span>
                </button>

                {{-- Résumé rapide de l'historique --}}
                <section class="card-hard rounded-2xl border-2 border-ink bg-panel p-5">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <h3 class="font-display text-base text-ink">Derniers tirages</h3>
                        <div class="flex items-center gap-3">
                            <a href="{{ route('history') }}?filter=number_roulette" class="font-mono text-[10px] uppercase tracking-widest text-info hover:underline">
                                Historique complet →
                            </a>
                            @if(count($history))
                            <button type="button" wire:click="clearHistory" wire:confirm="Vider l'historique de ce mode ?" class="font-mono text-[10px] uppercase tracking-widest text-subtle transition hover:text-danger">
                                Vider
                            </button>
                            @endif
                        </div>
                    </div>

                    @if(count($history))
                    <div class="space-y-2">
                        @foreach(array_slice(array_reverse($history), 0, 5) as $entry)
                        <div class="flex items-center justify-between rounded-xl border-2 border-ink bg-wash px-4 py-2.5 text-sm">
                            <span class="flex items-center gap-2 truncate">
                                <span @class([
                                    'flex h-5 w-5 shrink-0 items-center justify-center rounded-full border border-ink/30 font-mono text-[9px] font-bold',
                                    'bg-emerald-600 text-white' => $entry['color'] === 'green',
                                    'bg-red-600 text-white' => $entry['color'] === 'red',
                                    'bg-ink text-white' => $entry['color'] === 'black',
                                    ])>{{ $entry['result'] }}</span>
                                <span class="truncate font-semibold text-ink">{{ $entry['bet_type_label'] }}</span>
                            </span>
                            <span @class([
                                'shrink-0 rounded-md border px-2 py-0.5 font-mono text-[10px] font-bold',
                                'border-ink bg-secondary text-ink' => $entry['won'],
                                'border-danger/30 bg-danger/10 text-danger' => ! $entry['won'],
                                ])>
                                {{ $entry['payout'] > 0 ? '+' : '' }}{{ $entry['payout'] }}
                            </span>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="rounded-xl border-2 border-dashed border-line py-6 text-center">
                        <p class="text-sm font-medium text-muted">Aucun tirage pour l’instant</p>
                    </div>
                    @endif
                </section>
            </div>
        </div>
    </div>
</div>
