<section class="card-hard space-y-4 rounded-2xl border-2 border-ink bg-panel p-8 text-center">
    <div class="text-4xl" aria-hidden="true">🔁</div>
    @if($isDuel)
    <p class="font-mono text-[10px] uppercase tracking-widest text-subtle">Tour terminé</p>
    <h2 class="font-display text-2xl">Passez l’appareil à {{ $name($currentPlayer) }}</h2>
    <p class="text-sm text-muted">Le tour du joueur précédent est terminé. Ses essais et son résultat restent masqués. À vous de jouer sur le même nombre.</p>
    @elseif($isChooseSecretHandoff)
    <p class="font-mono text-[10px] uppercase tracking-widest text-subtle">Première manche terminée</p>
    <h2 class="font-display text-2xl">Passez l’appareil à {{ $name($secretSetter) }}</h2>
    <p class="text-sm text-muted">{{ $name($secretSetter) }} choisira un nombre que {{ $name(3 - $secretSetter) }} devra deviner.</p>
    @else
    <p class="font-mono text-[10px] uppercase tracking-widest text-subtle">Le nombre est choisi</p>
    <h2 class="font-display text-2xl">Passez l’appareil à {{ $name($guesser) }}</h2>
    <p class="text-sm text-muted">Le nombre est masqué. {{ $name($guesser) }} peut maintenant le deviner.</p>
    @endif
    <button type="button" wire:click="continueAfterHandoff" class="rounded-xl border-2 border-ink bg-primary px-6 py-3 font-display text-sm text-white">J’AI L’APPAREIL</button>
</section>
