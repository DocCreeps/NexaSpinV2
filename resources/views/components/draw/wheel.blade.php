@props([
    'segments' => [],
    'showLabels' => true,
    'event' => 'wheel-spin',
])

{{--
    Roue générique et réutilisable.

    - `segments` : array de ['name' => string, 'color' => string (css color), 'path' => string (SVG path "d"), 'labelTransform' => string (SVG transform)].
      C'est à l'appelant de calculer ces valeurs (voir App\Livewire\Draw\RandomDraw::segments()).
    - `showLabels` : affiche ou non les noms directement sur les parts.
    - `event` : nom de l'événement Livewire/Alpine à écouter pour déclencher la rotation.
      Permet d'avoir plusieurs roues indépendantes sur une même page.
      Le composant émet en retour "{event}-finished" une fois l'animation terminée,
      pour que le parent puisse révéler le résultat, réactiver un bouton, etc.

    Utilisation :
        <x-draw.wheel :segments="$this->segments" :show-labels="$this->showLabelsOnWheel()" />

        Puis, côté serveur : $this->dispatch('wheel-spin', rotation: $angleEnDegres);
--}}

<div
    {{ $attributes->class(['relative w-full max-w-[320px] mx-auto']) }}
    x-data="{ rotation: 0, spinning: false }"
    x-on:{{ $event }}.window="
        spinning = true;
        rotation += $event.detail.rotation;
        setTimeout(() => { spinning = false; $dispatch('{{ $event }}-finished') }, 4600);
    "
>
    {{-- Pointeur fixe en haut de la roue --}}
    <div class="absolute left-1/2 -translate-x-1/2 -top-2 z-10">
        <div class="w-0 h-0 border-l-[12px] border-l-transparent border-r-[12px] border-r-transparent border-t-[20px] border-t-black"></div>
    </div>

    <svg viewBox="0 0 300 300" class="w-full drop-shadow-lg">
        <g
            x-bind:style="`transform: rotate(${rotation}deg); transform-origin: 150px 150px; transition: transform 4.5s cubic-bezier(0.17, 0.67, 0.2, 1);`"
        >
            @foreach($segments as $segment)

                <path
                    d="{{ $segment['path'] }}"
                    fill="{{ $segment['color'] }}"
                    stroke="white"
                    stroke-width="1"
                />

                @if($showLabels)

                    <text
                        transform="{{ $segment['labelTransform'] }}"
                        text-anchor="middle"
                        class="fill-white text-[11px] font-medium"
                        style="paint-order: stroke; stroke: rgba(0,0,0,0.35); stroke-width: 2px;"
                    >
                        {{ \Illuminate\Support\Str::limit($segment['name'], 12) }}
                    </text>

                @endif

            @endforeach
        </g>

        <circle cx="150" cy="150" r="14" fill="white" stroke="#111" stroke-width="2" />
    </svg>
</div>
