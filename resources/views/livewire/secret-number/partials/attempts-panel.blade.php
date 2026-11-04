<section class="card-hard rounded-2xl border-2 border-ink bg-panel p-5">
    <div class="mb-4 flex items-center justify-between">
        <div>
            <h2 class="font-display text-lg">Essais</h2>
            <p class="mt-0.5 font-mono text-[9px] uppercase tracking-widest text-faint">
                @if($isPlaying && $isDuel){{ $name($currentPlayer) }}
                @elseif($isPlaying && $isVersus){{ $name($currentPlayer) }} · manche en cours
                @elseif($isPlaying)Votre partie
                @elseif($isFinished)Récapitulatif de la partie
                @elseLes propositions apparaîtront ici
                @endif
            </p>
        </div>
        @if($isPlaying)
        <span class="rounded-lg border-2 border-ink bg-wash px-2.5 py-1 font-mono text-xs font-bold">{{ $attempts[$currentPlayer] ?? 0 }}</span>
        @endif
    </div>

    @if($isPlaying)
        @php $activeGuesses = $guesses[$currentPlayer] ?? []; @endphp
        @if($activeGuesses)
        <div class="custom-scrollbar max-h-[340px] space-y-1.5 overflow-y-auto pr-1">
            @foreach(array_reverse($activeGuesses, true) as $index => $entry)
            <div class="flex items-center justify-between rounded-xl border-2 border-line bg-wash px-3 py-2 text-sm">
                <span class="font-mono text-subtle">Essai {{ $index + 1 }} · <strong class="text-ink">{{ $entry['number'] }}</strong></span>
                <span @class(['rounded-md border px-2 py-0.5 font-mono text-[10px] font-bold uppercase', 'border-ink bg-secondary text-ink' => $entry['hint'] === 'trouvé', 'border-line bg-panel text-ink' => $entry['hint'] === 'plus', 'border-danger/30 bg-danger/10 text-danger' => $entry['hint'] === 'moins'])>{{ $entry['hint'] }}</span>
            </div>
            @endforeach
        </div>
        @else
        <p class="rounded-xl border-2 border-dashed border-line py-6 text-center text-sm text-muted">Aucune proposition pour l’instant</p>
        @endif
    @elseif($isFinished && $isDuel)
    <p class="rounded-xl border-2 border-dashed border-line py-5 text-center text-sm text-muted">Le classement est affiché juste en dessous.</p>
    @elseif($isFinished && $isVersus)
    <div class="space-y-3">
        @foreach($versusResults as $leg => $result)
        <div class="rounded-xl border-2 border-line bg-wash p-3">
            <div class="flex items-center justify-between gap-2">
                <span class="font-display text-sm">{{ $result['name'] }} · {{ $result['attempts'] }} {{ \Illuminate\Support\Str::plural('essai', $result['attempts']) }}</span>
                <span @class(['rounded-md border px-2 py-0.5 font-mono text-[9px] font-bold uppercase', 'border-ink bg-secondary text-ink' => $result['found'], 'border-danger/30 bg-danger/10 text-danger' => ! $result['found']])>{{ $result['found'] ? 'Trouvé' : 'Non trouvé' }}</span>
            </div>
            <div class="mt-2 flex flex-wrap gap-1.5">
                @foreach($result['guesses'] as $guessEntry)
                <span class="rounded-md border border-line bg-panel px-2 py-1 font-mono text-[10px] text-subtle">{{ $guessEntry['number'] }} · {{ $guessEntry['hint'] }}</span>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>
    @elseif($isFinished && $isSolo)
    <div class="space-y-1.5">
        @foreach(array_reverse($guesses[1] ?? [], true) as $index => $entry)
        <div class="flex items-center justify-between rounded-xl border-2 border-line bg-wash px-3 py-2 text-sm">
            <span class="font-mono text-subtle">Essai {{ $index + 1 }} · <strong class="text-ink">{{ $entry['number'] }}</strong></span>
            <span class="rounded-md border border-line bg-panel px-2 py-0.5 font-mono text-[10px] font-bold uppercase text-subtle">{{ $entry['hint'] }}</span>
        </div>
        @endforeach
    </div>
    @elseif($isHandoff && $isDuel)
    <p class="rounded-xl border-2 border-dashed border-line py-5 text-center text-sm text-muted">Les essais du joueur précédent sont masqués.</p>
    @else
    <p class="rounded-xl border-2 border-dashed border-line py-5 text-center text-sm text-muted">Les essais apparaîtront pendant la manche.</p>
    @endif
</section>
