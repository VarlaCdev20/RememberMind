@props([
    'icon' => 'ph-squares-four',
    'title' => 'Módulo',
    'text' => 'Descripción del módulo',
    'color' => 'terracota',
])

@php
    $colors = [
        'terracota' => 'text-terracota bg-terracota/10 group-hover:bg-terracota group-hover:text-white',
        'azul' => 'text-azul-profundo bg-azul-clinico/25 group-hover:bg-azul-profundo group-hover:text-white',
        'verde' => 'text-[#8DA280] bg-[#8DA280]/15 group-hover:bg-[#8DA280] group-hover:text-white',
        'morado' => 'text-morado-cog bg-morado-cog/15 group-hover:bg-morado-cog group-hover:text-white',
    ];

    $style = $colors[$color] ?? $colors['terracota'];
@endphp

<div class="group cursor-pointer rounded-[2.5rem] border border-[#C7B5A3] bg-[#D5C7B9]/80 p-6 shadow-md transition-all duration-500 hover:-translate-y-2 hover:shadow-[0_25px_50px_rgba(47,62,92,0.22)]">
    <div class="mb-6 flex h-16 w-16 items-center justify-center rounded-2xl shadow-inner transition-all duration-500 group-hover:rotate-6 group-hover:rounded-full {{ $style }}">
        <i class="ph-fill {{ $icon }} text-3xl"></i>
    </div>

    <h3 class="text-xl font-black text-azul-profundo">
        {{ $title }}
    </h3>

    <p class="mt-3 text-sm font-bold leading-6 text-azul-profundo/65">
        {{ $text }}
    </p>

    <div class="mt-5 h-1.5 w-10 rounded-full bg-[#967B66]/30 transition-all duration-500 group-hover:w-full group-hover:bg-terracota"></div>
</div>