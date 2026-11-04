<section class="card-hard rounded-2xl border-2 border-ink bg-panel p-5">
    <div class="mb-4">
        <h2 class="font-display text-lg">{{ $isFinished ? 'Classement final' : 'Classement provisoire' }}</h2>
        <p class="mt-0.5 text-xs text-muted">Le classement évolue selon le nombre d’essais. Les propositions du joueur précédent restent masquées.</p>
    </div>
    @if($rankings)
    <div class="space-y-1.5">
        @foreach($rankings as $ranking)
        <div class="flex items-center justify-between rounded-xl border-2 border-line bg-wash px-3 py-2 text-sm">
            <span class="flex items-center gap-2"><span class="flex h-6 w-6 items-center justify-center rounded-full border border-ink bg-panel font-mono text-[10px] font-bold">{{ $ranking['rank'] }}</span><strong>{{ $ranking['name'] }}</strong></span>
            <span class="font-mono text-xs text-subtle">{{ $ranking['attempts'] }} {{ \Illuminate\Support\Str::plural('essai', $ranking['attempts']) }}@if(isset($ranking['found']) && ! $ranking['found']) · non trouvé @endif</span>
        </div>
        @endforeach
    </div>
    @else
    <p class="rounded-xl border-2 border-dashed border-line py-5 text-center text-sm text-muted">Le classement apparaîtra dès la première tentative.</p>
    @endif
</section>
