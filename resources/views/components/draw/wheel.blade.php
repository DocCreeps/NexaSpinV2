@props([
'segments' => [],
'showLabels' => true,
'event' => 'wheel-spin',
])

<div {{ $attributes->class(['relative w-full max-w-[320px] mx-auto']) }} x-data="{ rotation: 0, spinning: false }" x-on:{{ $event }}.window="
        spinning = true;
        rotation += $event.detail.rotation;
        setTimeout(() => { spinning = false; $dispatch('{{ $event }}-finished') }, 4600);
    ">
    {{-- Pointeur fixe en haut de la roue --}}
    <div class="absolute left-1/2 -translate-x-1/2 -top-2 z-10">
        <div class="w-0 h-0 border-l-[12px] border-l-transparent border-r-[12px] border-r-transparent border-t-[20px] border-t-black"></div>
    </div>

    <svg viewBox="0 0 300 300" class="w-full drop-shadow-lg">
        <g x-bind:style="`transform: rotate(${rotation}deg); transform-origin: 150px 150px; transition: transform 4.5s cubic-bezier(0.17, 0.67, 0.2, 1);`">
            @foreach($segments as $segment)

            @if($segment['fullCircle'] ?? false)
            <circle cx="150" cy="150" r="150" fill="{{ $segment['color'] }}" />
            @else
            <path d="{{ $segment['path'] }}" fill="{{ $segment['color'] }}" stroke="white" stroke-width="1" />
            @endif

            @if($showLabels)
            <text transform="{{ $segment['labelTransform'] }}" text-anchor="middle" class="fill-white text-[11px] font-medium" style="paint-order: stroke; stroke: rgba(0,0,0,0.35); stroke-width: 2px;">
                {{ \Illuminate\Support\Str::limit($segment['name'], 12) }}
            </text>
            @endif

            @endforeach
        </g>

        <circle cx="150" cy="150" r="10" fill="white" stroke="#111" stroke-width="1.5" />
    </svg>
</div>
