@props([
'participants' => [],
'colors' => [],
'locked' => false,
'error' => null,
])

<div {{ $attributes->class(['space-y-5']) }}>

    {{-- ZONE 1 : FORMULAIRE D'AJOUT --}}
    @unless($locked)
    <div class="space-y-3">
        <div class="flex gap-2">
            <input type="text" wire:model.blur="participant" wire:keydown.enter="addParticipant" placeholder="Nom du participant..." class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition placeholder:text-slate-400">

            <button wire:click="addParticipant" class="rounded-xl bg-indigo-600 hover:bg-indigo-700 active:scale-95 px-5 py-2.5 text-sm font-bold text-white shrink-0 shadow-sm transition">
                Ajouter
            </button>
        </div>

        @if($error)
        <p class="text-xs font-semibold text-red-500 flex items-center gap-1.5 px-1">
            ⚠️ {{ $error }}
        </p>
        @endif
    </div>
    @endunless

    {{-- SÉPARATEUR VISUEL --}}
    <div class="relative flex py-1 items-center">
        <div class="flex-grow border-t border-slate-100"></div>
        <span class="flex-shrink mx-4 text-[10px] font-bold uppercase tracking-wider text-slate-400">
            Membres inscrits ({{ count($participants) }})
        </span>
        <div class="flex-grow border-t border-slate-100"></div>
    </div>

    {{-- ZONE 2 : LISTE DES INSCRITS --}}
    <div class="space-y-1.5 max-h-[280px] overflow-y-auto pr-1">
        @forelse($participants as $index => $name)
        <div class="flex items-center justify-between rounded-xl border border-slate-100 bg-slate-50/30 px-4 py-2.5 text-sm hover:bg-slate-50/80 transition group">

            <span class="flex items-center gap-2.5 min-w-0">
                {{-- Affichage de la pastille de couleur correspondante à la roue --}}
                @if(isset($colors[$index]))
                <span class="inline-block w-2.5 h-2.5 rounded-full shrink-0" style="background-color: {{ $colors[$index] }}"></span>
                @endif

                <span class="font-semibold text-slate-700 truncate">
                    {{ $name }}
                </span>
            </span>

            @unless($locked)
            <button wire:click="removeParticipant({{ $index }})" class="text-slate-400 hover:text-red-500 p-1 rounded-lg hover:bg-red-50 transition" title="Supprimer">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
            @endunless

        </div>
        @empty
        <div class="p-6 text-center text-slate-400 text-sm">
            Aucun participant pour le moment.
        </div>
        @endforelse
    </div>

</div>
