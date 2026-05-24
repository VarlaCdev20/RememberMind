@props([
    'id',
    'titulo' => 'Gráfico',
    'subtitulo' => '',
    'icono' => 'ph-chart-line-up',
    'color' => 'terracota',
])

@php
$colores = [
    'terracota' => 'bg-terracota/10 text-terracota',
    'azul' => 'bg-azul-profundo/10 text-azul-profundo',
    'verde' => 'bg-[#8DA280]/15 text-[#8DA280]',
    'marron' => 'bg-[#967B66]/15 text-[#967B66]',
];

$estilo = $colores[$color] ?? $colores['terracota'];
@endphp

<div class="group h-full rounded-[1.8rem] border border-[#C7B5A3] bg-[#E6DDD3]/85 p-4 shadow-[0_14px_32px_rgba(47,62,92,0.11)] backdrop-blur-xl transition-all duration-300 hover:-translate-y-1 hover:shadow-[0_18px_42px_rgba(47,62,92,0.16)]">
    <div class="mb-4 flex items-center justify-between gap-3">
        <div>
            <h2 class="text-base font-black text-azul-profundo md:text-lg">
                {{ $titulo }}
            </h2>

            <p class="text-xs font-bold text-azul-profundo/55">
                {{ $subtitulo }}
            </p>
        </div>

        <div class="flex h-10 w-10 items-center justify-center rounded-xl shadow-inner transition-all duration-300 group-hover:rotate-6 group-hover:scale-105 {{ $estilo }}">
            <i class="ph-fill {{ $icono }} text-xl"></i>
        </div>
    </div>

    <div class="h-44 rounded-[1.4rem] bg-[#D5C7B9]/70 p-3 shadow-inner">
        <canvas id="{{ $id }}"></canvas>
    </div>
</div>