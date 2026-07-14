<x-layouts.app>
    <div class="min-h-screen lg:h-screen lg:overflow-hidden flex items-center justify-center bg-slate-50/50 relative antialiased selection:bg-indigo-500 selection:text-white">

        <!-- Orbes de lumière en arrière-plan -->
        <div class="absolute -top-24 -left-24 w-96 h-96 bg-indigo-200/30 rounded-full blur-[100px] pointer-events-none"></div>
        <div class="absolute top-1/4 -right-24 w-72 h-72 bg-purple-200/20 rounded-full blur-[80px] pointer-events-none"></div>

        <div class="relative w-full max-w-6xl mx-auto px-6 py-6 lg:py-8 flex flex-col justify-between h-full lg:max-h-[85vh]">

            {{-- Hero Section --}}
            <div class="text-center max-w-3xl mx-auto space-y-4">
                <div class="inline-flex items-center justify-center w-16 h-16 lg:w-20 lg:h-20 rounded-2xl bg-white shadow-sm border border-slate-100 text-3xl lg:text-4xl transition hover:rotate-12 duration-300 select-none">
                    🎲
                </div>
                <h1 class="text-4xl lg:text-6xl font-black tracking-tight text-slate-900">
                    Nexa<span class="bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">Spin</span>
                </h1>
                <p class="text-base lg:text-lg text-slate-600 font-medium max-w-xl mx-auto leading-relaxed">
                    Créez des tirages aléatoires, choisissez votre mode, et laissez la roue décider. 🚀
                </p>
            </div>

            {{-- Séparateur --}}
            <div class="my-6 flex items-center gap-4">
                <div class="h-px flex-1 bg-gradient-to-r from-transparent to-slate-200"></div>
                <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400 select-none">
                    Choisissez votre expérience
                </span>
                <div class="h-px flex-1 bg-gradient-to-l from-transparent to-slate-200"></div>
            </div>

            {{-- Grid des Cartes épuré --}}
            <div class="grid gap-4 lg:gap-6 md:grid-cols-3 items-stretch">
                @foreach($modes as $mode)
                <x-mode-card :mode="$mode" />
                @endforeach
            </div>

            {{-- Footer Info --}}
            <div class="mt-6 rounded-2xl bg-white/60 backdrop-blur-sm border border-slate-200/60 p-4 text-center shadow-sm max-w-2xl mx-auto">
                <p class="text-xs lg:text-sm text-slate-500 font-medium">
                    🎉 Organisez vos concours, jeux, décisions aléatoires et tirages au sort en quelques secondes.
                </p>
            </div>

        </div>
    </div>
</x-layouts.app>
