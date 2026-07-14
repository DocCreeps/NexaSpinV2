<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-indigo-50/50 relative overflow-hidden antialiased pb-24 selection:bg-indigo-500 selection:text-white">


    <div class="absolute top-[-10%] left-[-10%] w-[600px] h-[600px] bg-indigo-200/20 rounded-full blur-[140px] pointer-events-none"></div>

    <div class="absolute bottom-[-10%] right-[-10%] w-[500px] h-[500px] bg-purple-200/10 rounded-full blur-[120px] pointer-events-none"></div>



    <div class="max-w-7xl mx-auto px-6 py-10 space-y-8 relative z-10">


        {{-- HEADER --}}
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-5 bg-white/40 backdrop-blur-md border border-slate-200/50 rounded-3xl p-6 shadow-sm">

            <div>

                <a href="{{ route('home') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 hover:text-indigo-600 transition">

                    ← Retour aux modes

                </a>


                <h1 class="text-3xl font-black tracking-tight text-slate-900 flex items-center gap-2 mt-2">

                    🎡 Roue classique

                </h1>


                <p class="text-sm font-medium text-slate-500">
                    Un seul tour de roue pour choisir votre gagnant.
                </p>

            </div>



            <div class="inline-flex items-center gap-3 bg-slate-900 text-white rounded-2xl px-5 py-3 shadow-md">

                <span class="text-xl">
                    👥
                </span>


                <div>

                    <div class="text-lg font-black">
                        {{ count($participants) }}
                    </div>


                    <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">
                        participants
                    </div>

                </div>

            </div>


        </div>




        @if($error)

        <div class="rounded-2xl bg-red-50 border border-red-200 p-4 text-red-600">

            ⚠️ {{ $error }}

        </div>

        @endif





        <div class="grid lg:grid-cols-4 gap-8 items-start">



            {{-- PARTICIPANTS --}}
            <div class="lg:col-span-1 space-y-4">


                <div class="rounded-3xl bg-white border shadow-sm overflow-hidden">


                    <div class="p-5 border-b bg-slate-50">

                        <h2 class="font-bold">
                            📝 Gestion des inscrits
                        </h2>

                    </div>



                    <div class="p-5">


                        <x-draw.participant-form :participants="$participants" :colors="[]" :error="$error" />


                    </div>



                    <div class="max-h-[340px] overflow-y-auto divide-y">


                        @forelse($participants as $index=>$participant)


                        <div class="flex items-center gap-3 px-5 py-3">


                            @if(isset($this->segments[$index]['color']))

                            <span class="w-3 h-3 rounded-full" style="background: {{ $this->segments[$index]['color'] }}">
                            </span>

                            @endif



                            <span class="text-sm font-medium truncate">

                                {{ $participant }}

                            </span>


                        </div>


                        @empty


                        <div class="p-8 text-center text-slate-400">

                            Aucun participant.

                        </div>


                        @endforelse


                    </div>


                </div>





                {{-- ACTION --}}

                <button wire:click="draw" x-data="{spinning:false}" x-on:wheel-spin.window="spinning=true" x-on:wheel-spin-finished.window="spinning=false" :disabled="spinning" class="w-full rounded-2xl bg-gradient-to-r from-indigo-600 to-purple-600 py-4 text-white font-bold shadow-lg disabled:opacity-50">


                    <span x-show="!spinning">
                        ⚡ Lancer le tirage
                    </span>


                    <span x-show="spinning" x-cloak>
                        🎡 La roue tourne...
                    </span>


                </button>



            </div>







            {{-- ROUE --}}

            <div class="lg:col-span-3">


                <div class="rounded-[2rem] bg-white border shadow-sm p-8 min-h-[500px] flex flex-col" x-data="{finished:false}" x-on:wheel-spin.window="finished=false" x-on:wheel-spin-finished.window="finished=true">



                    <div class="flex-1 flex items-center justify-center">


                        <x-draw.wheel :segments="$this->segments" :show-labels="$this->showLabelsOnWheel()" />


                    </div>





                    @if($result)

                    <div x-show="finished" x-cloak class="mt-6 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 p-6 text-center text-white">


                        <div class="text-5xl">
                            🏆
                        </div>


                        <div class="uppercase text-xs font-bold">
                            Gagnant
                        </div>


                        <div class="text-4xl font-black">
                            {{ $result }}
                        </div>


                    </div>


                    @endif



                </div>


            </div>



        </div>


    </div>


</div>
