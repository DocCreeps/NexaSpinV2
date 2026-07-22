<x-layouts.app :title="$title" :meta-description="$metaDescription">
    <!-- Scroll actif uniquement sur mobile, verrouillé fixe à partir de md: -->
    <div class="min-h-dvh md:h-dvh w-full overflow-y-auto md:overflow-hidden py-6 md:py-0 flex items-center justify-center bg-slate-50/50 relative antialiased selection:bg-indigo-500 selection:text-white">

        <div class="absolute -top-24 -left-24 w-96 h-96 bg-indigo-200/30 rounded-full blur-[100px] pointer-events-none"></div>
        <div class="absolute top-1/4 -right-24 w-72 h-72 bg-purple-200/20 rounded-full blur-[80px] pointer-events-none"></div>

        <div class="relative w-full max-w-6xl mx-auto px-6 flex flex-col justify-between h-auto md:h-[90vh] md:max-h-[720px] lg:max-h-[800px] gap-6 md:gap-0">

            <div class="text-center max-w-3xl mx-auto space-y-3">
                <div class="inline-flex items-center justify-center w-14 h-14 lg:w-16 lg:h-16 rounded-2xl bg-white shadow-sm border border-slate-100 text-2xl lg:text-3xl transition hover:rotate-12 duration-300 select-none">
                    🎲
                </div>
                <h1 class="text-3xl lg:text-5xl font-black tracking-tight text-slate-900 leading-none">
                    Nexa<span class="bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">Spin</span>
                </h1>
                <p class="text-sm lg:text-base text-slate-600 font-medium max-w-xl mx-auto leading-relaxed">
                    Créez des tirages aléatoires, choisissez votre mode, et laissez la roue décider. 🚀
                </p>
            </div>

            <div class="my-2 flex items-center gap-4">
                <div class="h-px flex-1 bg-gradient-to-r from-transparent to-slate-200"></div>
                <span class="text-[9px] font-bold uppercase tracking-widest text-slate-400 select-none">
                    Choisissez votre expérience
                </span>
                <div class="h-px flex-1 bg-gradient-to-l from-transparent to-slate-200"></div>
            </div>

            <div class="grid gap-4 md:grid-cols-3 items-stretch">
                @foreach($modes as $mode)
                <x-mode-card :mode="$mode" />
                @endforeach
            </div>

            <div class="mt-4 rounded-xl bg-white/60 backdrop-blur-sm border border-slate-200/60 py-3 px-4 text-center shadow-sm max-w-2xl mx-auto w-full">
                <p class="text-xs text-slate-500 font-medium">
                    🎉 Organisez vos concours, jeux, décisions aléatoires et tirages au sort en quelques secondes.
                </p>
            </div>

        </div>
    </div>
</x-layouts.app>
