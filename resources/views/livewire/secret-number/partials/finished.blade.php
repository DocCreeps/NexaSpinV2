<section class="card-hard space-y-5 rounded-2xl border-2 border-ink bg-panel p-6 sm:p-8">
    <div class="text-center">
        <div class="text-4xl" aria-hidden="true">{{ $winner ? '🏆' : '🤝' }}</div>
        <p class="mt-2 font-mono text-[10px] uppercase tracking-widest text-subtle">{{ $modeLabel($activeMode) }} · Partie terminée</p>
        <h2 class="mt-1 font-display text-3xl">
            @if($isSolo){{ $winner ? 'Bravo, trouvé !' : 'Essais épuisés' }}
            @elseif($winner){{ $name($winner) }} gagne !
            @elseif(!$anyPlayerFound)Personne n’a trouvé le nombre.
            @else Égalité !
            @endif
        </h2>
        @if($isVersus)
        <p class="mt-2 text-sm text-muted">Manche 1 : <strong class="font-display text-xl text-ink">{{ $versusResults[0]['secret'] ?? '?' }}</strong> · Manche 2 : <strong class="font-display text-xl text-ink">{{ $versusResults[1]['secret'] ?? '?' }}</strong></p>
        @else
        <p class="mt-2 text-sm text-muted">Le nombre secret était <strong class="font-display text-xl text-ink">{{ $revealedNumber }}</strong></p>
        @endif
        @if($isDuel)
        <p class="mt-2 font-mono text-xs text-subtle">Classement des {{ $playerCount }} joueurs dans le panneau à droite.</p>
        @elseif($isSolo)
        <p class="mt-2 font-mono text-xs text-subtle">{{ $attempts[1] }} {{ \Illuminate\Support\Str::plural('essai', $attempts[1]) }}</p>
        @else
        <p class="mt-2 font-mono text-xs text-subtle">{{ $name(1) }} : {{ $versusResults[1]['attempts'] ?? 0 }} {{ \Illuminate\Support\Str::plural('essai', $versusResults[1]['attempts'] ?? 0) }} · {{ $name(2) }} : {{ $versusResults[0]['attempts'] ?? 0 }} {{ \Illuminate\Support\Str::plural('essai', $versusResults[0]['attempts'] ?? 0) }}</p>
        @endif
    </div>
    <div class="grid gap-2">
        <button type="button" wire:click="startGame" class="rounded-xl border-2 border-ink bg-primary px-5 py-3 font-display text-sm text-white">REJOUER</button>
    </div>
    <button type="button" wire:click="returnToModeSelection" class="w-full rounded-xl border-2 border-line bg-wash px-5 py-2.5 font-mono text-[10px] uppercase tracking-widest text-subtle transition hover:border-ink hover:text-ink">← Choisir un autre mode</button>
</section>
