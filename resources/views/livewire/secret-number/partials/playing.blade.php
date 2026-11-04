<section class="card-hard space-y-6 rounded-2xl border-2 border-ink bg-panel p-6 sm:p-8">
    <div class="text-center">
        @if($isDuel)
        <p class="font-mono text-xs uppercase tracking-widest text-subtle">Tour complet · {{ $name($currentPlayer) }} · {{ $currentPlayer }} sur {{ $playerCount }}</p>
        @elseif($isVersus)
        <p class="font-mono text-xs uppercase tracking-widest text-subtle">Au tour de {{ $name($currentPlayer) }}</p>
        @else
        <p class="font-mono text-xs uppercase tracking-widest text-subtle">À vous de deviner</p>
        @endif
        <p class="mt-3 font-display text-2xl">Entre {{ $activeMinimum }} et {{ $activeMaximum }}</p>
        <p class="mt-1 text-sm text-muted">{{ $activeMaxAttempts > 0 ? 'Limite : '.$activeMaxAttempts.' '.\Illuminate\Support\Str::plural('essai', $activeMaxAttempts) : 'Essais sans limite' }}</p>
    </div>

    @if($feedback)
    <div role="status" aria-live="polite" @class(['rounded-xl border-2 border-ink px-4 py-3 text-center font-display text-2xl', 'bg-secondary/30 text-ink' => str_contains($feedback, 'trouvé'), 'bg-wash text-ink' => !str_contains($feedback, 'trouvé')])>{{ $feedback }}</div>
    @endif

    <form wire:submit="submitGuess" class="mx-auto flex max-w-md flex-col gap-3 sm:flex-row">
        <input type="number" wire:model="guess" min="{{ $activeMinimum }}" max="{{ $activeMaximum }}" required inputmode="numeric" aria-label="Votre proposition" class="min-w-0 flex-1 rounded-xl border-2 border-ink bg-wash px-4 py-3 text-center font-mono text-xl focus:outline-none focus:ring-2 focus:ring-info">
        <button class="rounded-xl border-2 border-ink bg-primary px-6 py-3 font-display text-sm text-white">DEVINER</button>
    </form>

</section>
