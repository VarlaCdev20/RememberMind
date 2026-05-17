@props([
    'icon' => 'ph-chart-bar',
    'title' => 'Título',
    'value' => '0',
    'subtitle' => 'Descripción',
    'color' => 'terracota',
])

@php
    $styles = [
        'terracota' => 'text-terracota bg-terracota/10',
        'azul' => 'text-azul-profundo bg-azul-clinico/25',
        'verde' => 'text-[#8DA280] bg-[#8DA280]/15',
        'morado' => 'text-morado-cog bg-morado-cog/15',
    ];

    $style = $styles[$color] ?? $styles['terracota'];
@endphp

<div class="group rounded-[2.5rem] border border-[#C7B5A3] bg-[#E6DDD3]/80 p-6 shadow-lg backdrop-blur-xl transition-all duration-500 hover:-translate-y-2 hover:shadow-[0_25px_50px_rgba(47,62,92,0.22)]">
    <div class="mb-5 flex items-center justify-between">
        <div class="flex h-14 w-14 items-center justify-center rounded-2xl shadow-inner transition-all duration-500 group-hover:rotate-6 group-hover:rounded-full {{ $style }}">
            <i class="ph-fill {{ $icon }} text-3xl"></i>
        </div>

        <i class="ph-bold ph-arrow-up-right text-xl text-azul-profundo/25 transition group-hover:text-terracota"></i>
    </div>

    <p class="text-sm font-black uppercase tracking-widest text-azul-profundo/50">
        {{ $title }}
    </p>

    <h3 class="mt-2 text-4xl font-black text-azul-profundo">
        {{ $value }}
    </h3>

    <p class="mt-2 text-sm font-bold text-azul-profundo/60">
        {{ $subtitle }}
    </p>
</div>