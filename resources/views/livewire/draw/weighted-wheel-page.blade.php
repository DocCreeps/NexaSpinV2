<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-orange-50/50 relative overflow-hidden antialiased pb-24 selection:bg-orange-500 selection:text-white" x-data="{
         spinning: false,
         finished: false
     }" x-on:wheel-spin.window="spinning = true; finished = false" x-on:wheel-spin-finished.window="spinning = false; finished = true">

    {{-- EFFETS DE LUMIÈRE D'ARRIÈRE-PLAN --}}
    <div class="absolute top-[-10%] left-[-10%] w-[600px] h-[600px] bg-orange-200/20 rounded-full blur-[140px] pointer-events-none"></div>
    <div class="absolute bottom-[-10%] right-[-10%] w-[500px] h-[500px] bg-amber-200/10 rounded-full blur-[120px] pointer-events-none"></div>

    <div class="max-w-7xl mx-auto px-6 py-10 space-y-8 relative z-10">

        {{-- HEADER --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-5 bg-white/40 backdrop-blur-md border border-slate-200/50 rounded-3xl p-6 shadow-sm">
            <div>
                <a href="{{ route('home') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 hover:text-orange-600 transition">
                    ← Retour aux modes
                </a>
                <h1 class="text-3xl font-black tracking-tight text-slate-900 flex items-center gap-3 mt-2">
                    🎯 Tirage pondéré
                </h1>
                <p class="text-sm font-medium text-slate-500 mt-1">
                    Ajustez le poids de chaque participant pour influencer ses chances de gagner.
                </p>
            </div>

            <div class="flex gap-3">
                <div class="bg-white border border-slate-200/50 rounded-2xl px-5 py-3 shadow-sm min-w-[110px] text-center">
                    <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">
                        Participants
                    </div>
                    <div class="text-2xl font-black text-orange-600 mt-0.5">
                        {{ count($participants) }}
                    </div>
                </div>
            </div>
        </div>

        {{-- ALERTES ERREURS --}}
        @if($error)
        <div class="rounded-2xl bg-red-50 border border-red-200 p-4 text-red-600">
            ⚠️ {{ $error }}
        </div>
        @endif

        {{-- GRILLE PRINCIPALE --}}
        <div class="grid lg:grid-cols-12 gap-8 items-start">

            {{-- COLONNE GAUCHE : LA ROUE --}}
            <div class="lg:col-span-7">
                <div class="bg-white rounded-3xl shadow-sm border border-slate-200/50 p-8 flex flex-col items-center min-h-[520px] justify-center">

                    <div class="relative" wire:key="wheel-wrapper">
                        <div class="absolute inset-0 bg-orange-400/10 blur-3xl rounded-full"></div>
                        <x-draw.wheel :segments="$this->segments" :show-labels="$this->showLabelsOnWheel()" />
                    </div>

                    @if($result)
                    <div x-show="finished" x-cloak class="mt-8 px-8 py-3.5 rounded-full bg-gradient-to-r from-emerald-500 to-teal-500 text-white font-black text-lg shadow-md animate-bounce flex items-center gap-2">
                        🏆 Gagnant : {{ $result }}
                    </div>
                    @endif

                </div>
            </div>

            {{-- COLONNE DROITE : LE PANNEAU DE CONTRÔLE --}}
            <div class="lg:col-span-5 space-y-6">

                <div class="bg-white rounded-3xl shadow-sm border border-slate-200/50 p-5 overflow-hidden">
                    <h2 class="font-black text-lg text-slate-800 mb-4 flex items-center gap-2">
                        <span>👥</span> Configuration du salon
                    </h2>

                    <x-draw.participant-form :participants="$participants" :weights="$participantWeights" :colors="collect($this->segments)->pluck('color')->toArray()" :error="$error" theme="orange" />
                </div>

                <button wire:click="draw" wire:loading.attr="disabled" wire:target="draw" :disabled="spinning || {{ $this->canDraw() ? 'false' : 'true' }}" @disabled(! $this->canDraw()) class="w-full rounded-2xl py-4 font-black text-white shadow-md bg-gradient-to-r from-orange-600 to-amber-500 hover:from-orange-700 hover:to-amber-600 active:scale-[0.98] transition disabled:opacity-50 disabled:pointer-events-none">
                    <span x-show="!spinning">
                        @if(! $this->canDraw())
                        👥 Ajoutez au moins 3 participants
                        @else
                        ⚡ Lancer le tirage
                        @endif
                    </span>
                    <span x-show="spinning" x-cloak>
                        🎯 La roue tourne...
                    </span>
                </button>


            </div>

        </div>

    </div>
</div>
