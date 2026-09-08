<div class="min-h-screen w-full bg-surface text-ink antialiased selection:bg-secondary selection:text-ink">
    <div class="mx-auto max-w-3xl space-y-6 px-4 py-6 sm:space-y-8 sm:px-10 sm:py-10">

        {{-- HEADER --}}
        <header class="flex flex-col gap-4 border-b-4 border-ink pb-5 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 rounded text-sm font-semibold text-muted transition-colors hover:text-ink focus:outline-none focus-visible:ring-2 focus-visible:ring-info focus-visible:ring-offset-2">
                    ← Salle
                </a>

                <p class="mt-4 font-mono text-[10px] uppercase tracking-widest text-faint">
                    ◆ Lanceur façon jeu de rôle ◆
                </p>

                <h1 class="mt-1 font-display text-4xl leading-none text-ink sm:text-5xl">
                    Lanceur de dés
                </h1>

                <p class="mt-3 max-w-md text-sm text-muted">
                    Choisissez un type de dé et le nombre à lancer. Cet historique n’est pas
                    conservé au rechargement de la page.
                </p>
            </div>
        </header>

        {{-- ERREUR --}}
        @if($error)
        <div role="alert" class="rounded-xl border-2 border-ink bg-danger/10 px-4 py-3 text-sm font-semibold text-danger">
            ⚠ {{ $error }}
        </div>
        @endif

        <section class="card-hard rounded-2xl border-2 border-ink bg-panel p-6 sm:p-8">

            {{-- Type de dé --}}
            <p class="mb-2 font-mono text-[10px] uppercase tracking-widest text-subtle">Type de dé</p>
            <div class="mb-6 flex flex-wrap gap-2">
                @foreach($this->availableFaces() as $face)
                <button type="button" wire:click="selectFaces({{ $face->value }})" @class([
                    'rounded-xl border-2 border-ink px-4 py-2.5 font-display text-sm transition',
                    'bg-ink text-white shadow-hard' => $facesValue === $face->value,
                    'bg-wash text-ink hover:bg-panel' => $facesValue !== $face->value,
                    ])>
                    {{ $face->label() }}
                </button>
                @endforeach
            </div>

            {{-- Nombre de dés --}}
            <p class="mb-2 font-mono text-[10px] uppercase tracking-widest text-subtle">Nombre de dés</p>
            <div class="mb-6 flex items-center gap-2">
                <button type="button" wire:click="decrementDiceCount" class="h-10 w-10 shrink-0 rounded-lg border-2 border-ink bg-wash font-bold text-ink transition hover:bg-panel">−</button>
                <div class="flex-1 rounded-xl border-2 border-ink bg-wash py-2 text-center font-display text-lg text-ink">
                    {{ $diceCount }}
                </div>
                <button type="button" wire:click="incrementDiceCount" class="h-10 w-10 shrink-0 rounded-lg border-2 border-ink bg-wash font-bold text-ink transition hover:bg-panel">+</button>
            </div>

            <button type="button" wire:click="roll" class="btn-press w-full rounded-xl border-2 border-ink bg-primary py-3.5 font-display text-sm text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-info focus-visible:ring-offset-2">
                ▶ LANCER {{ $diceCount }}D{{ $facesValue }}
            </button>

            {{-- Résultat --}}
            @if($lastResult)
            <div class="mt-6 rounded-xl border-2 border-ink bg-secondary/20 p-4">
                <div class="mb-3 flex flex-wrap justify-center gap-2">
                    @foreach($lastResult['values'] as $value)
                    <span class="flex h-12 w-12 items-center justify-center rounded-lg border-2 border-ink bg-panel font-display text-lg text-ink shadow-hard">
                        {{ $value }}
                    </span>
                    @endforeach
                </div>
                <p class="text-center font-display text-2xl text-ink">
                    Total : {{ $lastResult['sum'] }}
                </p>
            </div>
            @endif
        </section>

        {{-- HISTORIQUE DE SESSION (non persisté) --}}
        @if(count($rolls))
        <section class="card-hard rounded-2xl border-2 border-ink bg-panel p-5">
            <div class="mb-3 flex items-center justify-between">
                <h3 class="font-display text-base text-ink">Lancers de cette session</h3>
                <button type="button" wire:click="clearRolls" class="font-mono text-[10px] uppercase tracking-widest text-subtle transition hover:text-danger">
                    Vider
                </button>
            </div>

            <div class="max-h-64 space-y-1.5 overflow-y-auto pr-1">
                @foreach(array_reverse($rolls) as $entry)
                <div class="flex items-center justify-between rounded-xl border-2 border-line bg-wash px-3 py-2 text-sm">
                    <span class="font-mono text-xs text-muted">
                        {{ count($entry['values']) }}d{{ $entry['faces'] }} : {{ implode(' · ', $entry['values']) }}
                    </span>
                    <span class="font-display text-sm text-ink">{{ $entry['sum'] }}</span>
                </div>
                @endforeach
            </div>
        </section>
        @endif
    </div>
</div>
