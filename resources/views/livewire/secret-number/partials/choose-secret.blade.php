<section class="card-hard space-y-4 rounded-2xl border-2 border-ink bg-panel p-6 sm:p-8">
    <p class="font-mono text-[10px] uppercase tracking-widest text-subtle">Versus · {{ $name($secretSetter) }}</p>
    <h2 class="font-display text-2xl">{{ $name($secretSetter) }}, choisissez le nombre secret</h2>
    <p class="text-sm text-muted">{{ $name($guesser) }} ne doit pas regarder l’écran. Il devra deviner ce nombre sans le voir.</p>
    <form wire:submit="submitSecret" class="flex flex-col gap-3 sm:flex-row">
        <input type="number" wire:model="secretInput" min="{{ $activeMinimum }}" max="{{ $activeMaximum }}" required aria-label="Nombre secret" class="min-w-0 flex-1 rounded-xl border-2 border-ink bg-wash px-4 py-3 text-center font-mono text-lg focus:outline-none focus:ring-2 focus:ring-info">
        <button class="rounded-xl border-2 border-ink bg-primary px-6 py-3 font-display text-sm text-white">VALIDER</button>
    </form>
</section>
